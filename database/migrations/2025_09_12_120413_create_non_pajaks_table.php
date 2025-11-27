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
        Schema::create('non_pajaks', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->nullable();
            $table->string('no_invoice_non_pajak')->unique();
            $table->string('no_surat_jalan')->unique();
            $table->foreignId('sales_id')
                ->constrained('sales')
                ->onDelete('restrict');
            $table->foreignId('toko_id')
                ->constrained('tokos')
                ->onDelete('restrict');
            $table->text('remarks')->nullable();
            $table->string('pembayaran')->nullable(); // Cash / BCA / Mandiri
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_pajaks');
    }
};
