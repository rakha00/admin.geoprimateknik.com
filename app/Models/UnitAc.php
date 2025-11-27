<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitAc extends Model
{
    use HasFactory;

    /**
     * Kolom-kolom yang boleh diisi lewat mass assignment (Model::create, ->fill, dsb).
     */
    protected $fillable = [
        'sku',
        'nama_unit',
        'harga_modal',
        'stock_awal',
    ];

    /**
     * Detail barang masuk untuk unit ini.
     */
    public function barangMasukDetails(): HasMany
    {
        return $this->hasMany(\App\Models\BarangMasukDetail::class, 'unit_ac_id');
    }

    /**
     * Detail transaksi produk (keluar) untuk unit ini.
     *
     * Menggunakan 'sku' sebagai foreign key di tabel transaksi_produk_details,
     * yang dicocokkan dengan kolom 'sku' di tabel unit_ac.
     */
    public function transaksiProdukDetails(): HasMany
    {
        return $this->hasMany(
            \App\Models\TransaksiProdukDetail::class,
            'sku',    // foreign key di transaksi_produk_details
            'sku'     // local key di unit_ac
        );
    }
}
