<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pajak extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_invoice',
        'no_surat_jalan',
        'tanggal',
        'sales_id',
        'toko_id',
        'pembayaran',
        'remarks',
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

