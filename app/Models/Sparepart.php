<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sparepart extends Model
{
    use HasFactory;

    protected $fillable = [
        'sku',
        'nama_sparepart',
        'harga_modal',
        'satuan',
        'stock_awal',
        'stok_masuk',
        'stok_keluar',
        'stok_akhir',
    ];
    public function recalculateStock()
    {
        $stokMasuk = $this->stok_masuk; // Assuming this is managed elsewhere or static for now
        // Or should we calculate stok_masuk too? The user didn't specify, but usually stock is calculated from transactions.
        // However, looking at UnitAc logic (from previous context), it might be similar.
        // For now, let's just update stok_keluar and stok_akhir.

        $this->stok_keluar = \App\Models\SparepartKeluarDetail::where('sparepart_id', $this->id)->sum('jumlah_keluar');
        $this->stok_akhir = $this->stock_awal + $this->stok_masuk - $this->stok_keluar;
        $this->save();
    }
}
