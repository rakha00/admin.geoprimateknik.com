<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NonPajakDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'non_pajak_id',
        'sku',
        'nama_unit',
        'unit_ac_id',
        'harga_modal',
        'harga_jual',
        'total_harga_jual',
        'jumlah_keluar',
        'keuntungan',
        'remarks',
    ];

    protected static function booted()
    {
        static::saved(function ($detail) {
            $detail->syncPiutang();
            if ($detail->unit_ac_id) {
                $unit = \App\Models\UnitAc::find($detail->unit_ac_id);
                $unit?->recalculateStock();
            }
        });

        static::deleted(function ($detail) {
            $detail->syncPiutang();
            if ($detail->unit_ac_id) {
                $unit = \App\Models\UnitAc::find($detail->unit_ac_id);
                $unit?->recalculateStock();
            }
        });
    }

    public function nonPajak()
    {
        return $this->belongsTo(NonPajak::class);
    }

    protected function syncPiutang()
    {
        $nonPajak = $this->nonPajak;
        if (!$nonPajak)
            return;

        $totalHargaJual = $nonPajak->details()->sum('total_harga_jual');

        \App\Models\Piutang::updateOrCreate(
            ['non_pajak_id' => $nonPajak->id], // pakai non_pajak_id
            [
                'due_date' => $nonPajak->tanggal,
                'total_harga_modal' => $totalHargaJual,
                'status_pembayaran' => 'belum lunas',
                'keterangan' => 'Non Pajak',
            ]
        );
    }
}
