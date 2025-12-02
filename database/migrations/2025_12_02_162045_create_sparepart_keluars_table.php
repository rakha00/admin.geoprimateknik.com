<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sparepart_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('no_invoice')->unique();
            $table->string('no_surat_jalan')->unique();
            $table->date('tanggal');
            $table->foreignId('sales_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('toko_id')->constrained('tokos')->cascadeOnDelete();
            $table->string('pembayaran');
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sparepart_keluars');
    }
};
