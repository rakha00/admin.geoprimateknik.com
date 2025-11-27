<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasukDetail extends Model
{
    protected $fillable = [
        'barang_masuk_id',
        'unit_ac_id',
        'sku',
        'nama_unit',
        'harga_jual',
        'jumlah_barang_masuk',
        'remarks',
        'harga_modal',
    ];

    protected static function booted()
    {
        static::creating(function ($detail) {
            if ($detail->unit_ac_id) {
                $unit = \App\Models\UnitAc::find($detail->unit_ac_id);
                if ($unit) {
                    $detail->sku = $unit->sku;
                    $detail->nama_unit = $unit->nama_unit;
                    $detail->harga_modal = $unit->harga_modal;
                }
            }
        });

        // trigger otomatis ke utang
        static::saved(function ($detail) {
            $detail->syncUtang();
        });

        static::deleted(function ($detail) {
            $detail->syncUtang();
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

    protected function syncUtang()
    {
        $barangMasuk = $this->barangMasuk;
        if (! $barangMasuk) return;

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
