<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SparepartKeluarDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sparepart_keluar_id',
        'sparepart_id',
        'sku',
        'nama_sparepart',
        'harga_modal',
        'harga_jual',
        'jumlah_keluar',
        'total_harga_jual',
        'keuntungan',
        'remarks',
    ];

    protected static function booted()
    {
        static::saved(function ($detail) {
            $detail->syncPiutang();
            if ($detail->sparepart_id) {
                $sparepart = \App\Models\Sparepart::find($detail->sparepart_id);
                $sparepart?->recalculateStock();
            }
        });

        static::deleted(function ($detail) {
            $detail->syncPiutang();
            if ($detail->sparepart_id) {
                $sparepart = \App\Models\Sparepart::find($detail->sparepart_id);
                $sparepart?->recalculateStock();
            }
        });
    }

    public function sparepartKeluar()
    {
        return $this->belongsTo(SparepartKeluar::class);
    }

    public function sparepart()
    {
        return $this->belongsTo(Sparepart::class);
    }

    protected function syncPiutang()
    {
        $sparepartKeluar = $this->sparepartKeluar;
        if (!$sparepartKeluar)
            return;

        $totalHargaJual = $sparepartKeluar->details()->sum('total_harga_jual');

        \App\Models\Piutang::updateOrCreate(
            ['sparepart_keluar_id' => $sparepartKeluar->id],
            [
                'due_date' => $sparepartKeluar->tanggal,
                'total_harga_modal' => $totalHargaJual, // Note: Logic might need adjustment if total_harga_modal means something else in Piutang context, but following pattern.
                // Wait, in Piutang model, total_harga_modal seems to be the total amount to be paid? 
                // In PajakDetail: $piutang->total_harga_modal = $pajak->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
                // So it is the total sales amount.
                'status_pembayaran' => 'belum lunas',
                'keterangan' => 'Sparepart Keluar',
            ]
        );
    }
}
