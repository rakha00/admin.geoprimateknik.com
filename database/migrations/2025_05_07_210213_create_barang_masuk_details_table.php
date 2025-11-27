<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barang_masuk_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_masuk_id')
                  ->constrained('barang_masuks')
                  ->cascadeOnDelete();
            $table->foreignId('unit_ac_id')
                  ->constrained('unit_acs')
                  ->cascadeOnDelete();
            // Jadikan nullable agar tidak wajib diset
            $table->string('sku')->nullable();
            $table->string('nama_unit')->nullable();
            $table->integer('harga_modal');
            $table->integer('jumlah_barang_masuk');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_masuk_details');
    }
};
