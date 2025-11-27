<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('piutangs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pajak_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('non_pajak_id')->nullable()->constrained()->cascadeOnDelete();

            $table->date('due_date')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status_pembayaran', ['belum lunas', 'tercicil', 'sudah lunas'])
                  ->default('belum lunas');
            $table->json('fotos')->nullable();
            // Saldo sudah dibayar
            $table->decimal('sudah_dibayar', 15, 2)->default(0);
            $table->decimal('total_harga_modal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('piutangs');
    }
};