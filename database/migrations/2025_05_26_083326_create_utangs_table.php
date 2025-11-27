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
        Schema::create('utangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_masuk_id')->constrained()->cascadeOnDelete(); // ganti ini
            $table->date('due_date');
            $table->text('keterangan')->nullable();
            $table->enum('status_pembayaran', ['belum lunas', 'tercicil', 'sudah lunas'])->default('belum lunas');
            $table->json('fotos')->nullable();
            $table->decimal('sudah_dibayar', 15, 2)->default(0);
            $table->decimal('total_harga_modal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utangs');
    }
};
