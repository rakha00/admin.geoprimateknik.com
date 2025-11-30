<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitAc extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function ($unit) {
            // Avoid infinite loop since recalculateStock uses updateQuietly
            // But we should be careful. 
            // recalculateStock updates 'stok_keluar' and 'stok_akhir'.
            // If we are saving those, we don't need to recalculate?
            // Actually, if we change stock_awal, we need to recalculate.

            // To be safe and simple:
            // We can just call recalculateStock. Since it uses updateQuietly, it won't trigger 'saved' again.
            $unit->recalculateStock();
        });
    }

    /**
     * Kolom-kolom yang boleh diisi lewat mass assignment (Model::create, ->fill, dsb).
     */
    protected $fillable = [
        'sku',
        'nama_unit',
        'harga_modal',
        'stock_awal',
        'stok_keluar',
        'stok_akhir',
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

    public function pajakDetails(): HasMany
    {
        return $this->hasMany(PajakDetail::class, 'unit_ac_id');
    }

    public function nonPajakDetails(): HasMany
    {
        return $this->hasMany(NonPajakDetail::class, 'unit_ac_id');
    }

    public function recalculateStock(): void
    {
        // 1. Hitung total masuk dari BarangMasukDetail
        //    (Asumsi: relasi barangMasukDetails sudah ada & benar)
        $totalMasuk = $this->barangMasukDetails()->sum('jumlah_barang_masuk');

        // 2. Hitung total keluar dari:
        //    - TransaksiProdukDetail (jika masih dipakai)
        //    - PajakDetail
        //    - NonPajakDetail
        $keluarTransaksi = $this->transaksiProdukDetails()->sum('jumlah_keluar');
        $keluarPajak = $this->pajakDetails()->sum('jumlah_keluar');
        $keluarNonPajak = $this->nonPajakDetails()->sum('jumlah_keluar');

        $totalKeluar = $keluarTransaksi + $keluarPajak + $keluarNonPajak;

        // 3. Hitung stok akhir
        //    Stok Akhir = Stok Awal + Total Masuk - Total Keluar
        $stokAkhir = ($this->stock_awal ?? 0) + $totalMasuk - $totalKeluar;

        // 4. Simpan ke kolom baru
        $this->updateQuietly([
            'stok_keluar' => $totalKeluar,
            'stok_akhir' => $stokAkhir,
        ]);
    }
}
