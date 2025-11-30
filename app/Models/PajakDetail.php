<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PajakDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pajak_id',
        'sku',
        'nama_unit',
        'unit_ac_id',
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

    public function pajak()
    {
        return $this->belongsTo(Pajak::class);
    }

    protected function syncPiutang()
    {
        $pajak = $this->pajak;
        if (!$pajak)
            return;

        $totalHargaJual = $pajak->details()->sum('total_harga_jual');

        \App\Models\Piutang::updateOrCreate(
            ['pajak_id' => $pajak->id], // pakai pajak_id, bukan no_invoice
            [
                'due_date' => $pajak->tanggal,
                'total_harga_modal' => $totalHargaJual,
                'status_pembayaran' => 'belum lunas',
                'keterangan' => 'Pajak',
            ]
        );
    }

}
