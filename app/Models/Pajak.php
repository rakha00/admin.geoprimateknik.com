<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pajak extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($pajak) {
            // Jika status berubah, hitung ulang stok unit terkait
            if ($pajak->isDirty('status')) {
                foreach ($pajak->details as $detail) {
                    if ($detail->unitAc) {
                        $detail->unitAc->recalculateStock();
                    }
                }
            }
        });
    }

    protected $fillable = [
        'no_invoice',
        'no_surat_jalan',
        'tanggal',
        'sales_id',
        'toko_id',
        'pembayaran',
        'remarks',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(PajakDetail::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }
}
