<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_konsumen',
        'no_hp',
        'alamat',       // sesuai nama kolom di database
        'remarks',
    ];

    public function transaksiProduks()
    {
        return $this->hasMany(TransaksiProduk::class, 'id_toko');
    }

    public function transaksiProdukFix()
{
    return $this->hasMany(TransaksiProduk::class, 'toko_id');
}

}
