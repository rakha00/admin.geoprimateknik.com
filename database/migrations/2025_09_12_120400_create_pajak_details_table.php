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
        Schema::create('pajak_details', function (Blueprint $table) {
            $table->id();

            // relasi ke header pajak
            $table->foreignId('pajak_id')
                ->constrained('pajaks')
                ->onDelete('cascade');

            // data detail
            $table->string('sku')->nullable();
            $table->string('nama_unit')->nullable();
            $table->unsignedBigInteger('unit_ac_id')->nullable();

            // kolom tambahan
            $table->integer('harga_modal')->default(0); // manual by admin

            // kolom hitungan
            $table->integer('harga_jual')->default(0);
            $table->integer('keuntungan')->default(0);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->integer('jumlah_keluar')
                ->default(0);
            $table->integer('total_harga_jual')->default(0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pajak_details');
    }
};
