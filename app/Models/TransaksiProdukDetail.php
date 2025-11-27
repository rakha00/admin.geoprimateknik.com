<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiProdukDetail extends Model
{
    protected $fillable = [
        'sku',
        'nama_unit',
        'harga_modal',
        'harga_jual',
        'jumlah_keluar',
        'remarks',
        // jangan masukkan total_* di $fillable
    ];


    public function transaksiProduk()
    {
        return $this->belongsTo(TransaksiProduk::class);
    }

    // Relationship ke UnitAc berdasarkan SKU
    public function unitAc()
    {
        return $this->belongsTo(UnitAc::class, 'sku', 'sku');
    }
        

    protected static function booted()
    {
        static::creating(function ($detail) {
            $detail->total_modal      = $detail->harga_modal  * $detail->jumlah_keluar;
            $detail->total_harga_jual = $detail->harga_jual  * $detail->jumlah_keluar;
            $detail->keuntungan       = $detail->total_harga_jual - $detail->total_modal;
        });
    }
}
