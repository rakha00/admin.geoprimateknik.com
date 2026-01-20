<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasukDetail extends Model
{
    protected $fillable = [
        'barang_masuk_id',
        'unit_ac_id',
        'sparepart_id',
        'sku',
        'nama_unit',
        'harga_jual',
        'jumlah_barang_masuk',
        'remarks',
        'harga_modal',
    ];

    protected static function booted()
    {
        static::saving(function ($detail) {
            if ($detail->unit_ac_id) {
                $unit = \App\Models\UnitAc::find($detail->unit_ac_id);
                if ($unit) {
                    $detail->sku = $unit->sku;
                    $detail->nama_unit = $unit->nama_unit;
                    $detail->harga_modal = $unit->harga_modal;
                }
            } elseif ($detail->sparepart_id) {
                $sparepart = \App\Models\Sparepart::find($detail->sparepart_id);
                if ($sparepart) {
                    $detail->sku = $sparepart->sku;
                    $detail->nama_unit = $sparepart->nama_sparepart;
                    $detail->harga_modal = $sparepart->harga_modal;
                }
            }
        });

        // trigger otomatis ke utang dan update stock sparepart
        static::saved(function ($detail) {
            $detail->syncUtang();
            $detail->updateSparepartStock();
            $detail->updateUnitAcStock();
        });

        static::deleted(function ($detail) {
            $detail->syncUtang();
            $detail->updateSparepartStock();
            $detail->updateUnitAcStock();
        });
    }

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class);
    }

    public function unitAc()
    {
        return $this->belongsTo(UnitAc::class);
    }

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    public function updateSparepartStock()
    {
        // Handle current sparepart
        if ($this->sparepart_id) {
            $this->recalculateStock($this->sparepart_id);
        }

        // Handle old sparepart if changed
        $originalId = $this->getOriginal('sparepart_id');
        if ($originalId && $originalId != $this->sparepart_id) {
            $this->recalculateStock($originalId);
        }
    }

    public function updateUnitAcStock()
    {
        if ($this->unit_ac_id) {
            $unit = \App\Models\UnitAc::find($this->unit_ac_id);
            if ($unit) {
                $unit->recalculateStock();
            }
        }

        $originalId = $this->getOriginal('unit_ac_id');
        if ($originalId && $originalId != $this->unit_ac_id) {
            $unit = \App\Models\UnitAc::find($originalId);
            if ($unit) {
                $unit->recalculateStock();
            }
        }
    }

    protected function recalculateStock($sparepartId)
    {
        $sparepart = \App\Models\Sparepart::find($sparepartId);
        if ($sparepart) {
            $stokMasuk = \App\Models\BarangMasukDetail::where('sparepart_id', $sparepartId)
                ->whereHas('barangMasuk', function ($q) {
                    $q->where('status', 'Selesai');
                })
                ->sum('jumlah_barang_masuk');
            $sparepart->stok_masuk = $stokMasuk;
            $sparepart->stok_akhir = $sparepart->stock_awal + $stokMasuk - $sparepart->stok_keluar;
            $sparepart->save();
        }
    }

    public function syncUtang()
    {
        $barangMasuk = $this->barangMasuk;
        if (!$barangMasuk || $barangMasuk->status !== 'Selesai') {
            return;
        }

        $totalHargaModal = $barangMasuk->barangMasukDetails->sum(function ($d) {
            $harga = $d->harga_modal ?? 0;
            $jumlah = $d->jumlah_barang_masuk ?? 0;

            return $harga * $jumlah;
        });

        \App\Models\Utang::updateOrCreate(
            ['barang_masuk_id' => $barangMasuk->id],
            [
                'due_date' => now()->addDays(30), // default jatuh tempo, bisa lu ganti
                'total_harga_modal' => $totalHargaModal,
                'status_pembayaran' => 'belum lunas',
                'keterangan' => 'Barang Masuk',
            ]
        );
    }
}
