<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Sales;
use App\Models\Toko;
use App\Models\TransaksiProdukDetail;

class TransaksiProduk extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'no_invoice',
        'no_surat_jalan',
        'tanggal',
        'sales_id',
        'toko_id',
        'remarks',
    ];

    /**
     * @return BelongsTo<Sales, TransaksiProduk>
     */
    public function sales(): BelongsTo
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    /**
     * @return BelongsTo<Toko, TransaksiProduk>
     */
    public function toko(): BelongsTo
    {
        return $this->belongsTo(Toko::class, 'toko_id');
    }

    /**
     * @return HasMany<TransaksiProdukDetail>
     */
    public function details(): HasMany
    {
        return $this->hasMany(
            TransaksiProdukDetail::class,
            'transaksi_produk_id', // foreign key on transaksi_produk_details
            'id'                   // local key on this table
        );
    }
}
