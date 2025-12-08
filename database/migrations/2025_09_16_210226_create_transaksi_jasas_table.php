<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('transaksi_jasas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_transaksi')->nullable();
            $table->string('no_invoice')->nullable();
            $table->string('no_surat_jalan')->nullable();

            $table->foreignId('teknisi_id')->nullable()->constrained('teknisis')->nullOnDelete();
            $table->foreignId('helper_id')->nullable()->constrained('helpers')->nullOnDelete();
            $table->foreignId('konsumen_jasa_id')->nullable()->constrained('konsumen_jasas')->nullOnDelete();

            $table->decimal('total_pendapatan_jasa', 15, 2)->nullable();
            $table->decimal('total_pengeluaran_jasa', 15, 2)->nullable();
            $table->decimal('total_keuntungan_jasa', 15, 2)->nullable();
            $table->text('keluhan')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('transaksi_jasas');
    }
};
