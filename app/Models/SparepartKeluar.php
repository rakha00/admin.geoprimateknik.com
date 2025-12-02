<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparepartKeluar extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'no_invoice',
        'no_surat_jalan',
        'tanggal',
        'sales_id',
        'toko_id',
        'pembayaran',
        'remarks',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function details()
    {
        return $this->hasMany(SparepartKeluarDetail::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class);
    }

    public function piutang()
    {
        return $this->hasOne(Piutang::class);
    }
}
