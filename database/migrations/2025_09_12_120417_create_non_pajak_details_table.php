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
        Schema::create('non_pajak_details', function (Blueprint $table) {
            $table->id();

            // relasi ke header non pajak
            $table->foreignId('non_pajak_id')
                ->constrained('non_pajaks')
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
            $table->integer('total_harga_jual')->default(0);

            $table->integer('jumlah_keluar')
                ->default(0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_pajak_details');
    }
};
