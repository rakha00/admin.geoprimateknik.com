<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi_produk_details', function (Blueprint $table) {
            // Tambahkan kolom hanya jika belum ada, untuk menghindari error duplicate
            if (! Schema::hasColumn('transaksi_produk_details', 'harga_modal')) {
                $table->integer('harga_modal')
                      ->default(0)
                      ->after('nama_unit');
            }

            if (! Schema::hasColumn('transaksi_produk_details', 'harga_jual')) {
                $table->integer('harga_jual')
                      ->default(0)
                      ->after('harga_modal');
            }

            if (! Schema::hasColumn('transaksi_produk_details', 'jumlah_keluar')) {
                $table->integer('jumlah_keluar')
                      ->default(0)
                      ->after('harga_jual');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_produk_details', function (Blueprint $table) {
            // Hapus kolom hanya jika memang ada
            if (Schema::hasColumn('transaksi_produk_details', 'jumlah_keluar')) {
                $table->dropColumn('jumlah_keluar');
            }

            if (Schema::hasColumn('transaksi_produk_details', 'harga_jual')) {
                $table->dropColumn('harga_jual');
            }

            if (Schema::hasColumn('transaksi_produk_details', 'harga_modal')) {
                $table->dropColumn('harga_modal');
            }
        });
    }
};
