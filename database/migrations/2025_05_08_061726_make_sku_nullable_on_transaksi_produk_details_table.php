<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MakeSkuNullableOnTransaksiProdukDetailsTable extends Migration
{
    public function up(): void
    {
        // 1) Pastikan tidak ada NULL di kolom yang hendak dirubah
        DB::table('transaksi_produk_details')
            ->whereNull('sku')
            ->update(['sku' => '']);

        DB::table('transaksi_produk_details')
            ->whereNull('nama_unit')
            ->update(['nama_unit' => '']);

        DB::table('transaksi_produk_details')
            ->whereNull('unit_ac_id')
            ->update(['unit_ac_id' => 0]);

        // 2) Ubah kolom menjadi nullable
        Schema::table('transaksi_produk_details', function (Blueprint $table) {
            $table->string('sku')->nullable()->change();
            $table->string('nama_unit')->nullable()->change();
            $table->unsignedBigInteger('unit_ac_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // 1) Kembalikan semua nilai kosong ke NULL jika perlu
        DB::table('transaksi_produk_details')
            ->where('sku', '')
            ->update(['sku' => null]);

        DB::table('transaksi_produk_details')
            ->where('nama_unit', '')
            ->update(['nama_unit' => null]);

        DB::table('transaksi_produk_details')
            ->where('unit_ac_id', 0)
            ->update(['unit_ac_id' => null]);

        // 2) Ubah kolom kembali menjadi NOT NULL
        Schema::table('transaksi_produk_details', function (Blueprint $table) {
            $table->string('sku')->nullable(false)->change();
            $table->string('nama_unit')->nullable(false)->change();
            $table->unsignedBigInteger('unit_ac_id')->nullable(false)->change();
        });
    }
}
