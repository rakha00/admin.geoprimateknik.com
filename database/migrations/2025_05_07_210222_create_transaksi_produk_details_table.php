<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaksiProdukDetailsTable extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_produk_details', function (Blueprint $table) {
            $table->id();

            // relasi ke header transaksi_produk
            $table->foreignId('transaksi_produk_id')
                  ->constrained('transaksi_produks')
                  ->onDelete('cascade');

            // data detail
            $table->string('sku')->nullable();
            $table->string('nama_unit')->nullable();
            $table->unsignedBigInteger('unit_ac_id')->nullable();

            // kolom hitungan dengan default 0
            $table->integer('total_modal')->default(0);
            $table->integer('total_harga_jual')->default(0);
            $table->integer('keuntungan')->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_produk_details');
    }
}
