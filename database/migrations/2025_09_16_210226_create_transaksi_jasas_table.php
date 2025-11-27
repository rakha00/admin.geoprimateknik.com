<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('transaksi_jasas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('konsumen_jasa_id')
                ->nullable()
                ->constrained('konsumen_jasas')
                ->onDelete('set null');
            $table->foreignId('teknisi_id')
                ->nullable()
                ->constrained('teknisis')
                ->onDelete('set null');
            $table->foreignId('helper_id')
                ->nullable()
                ->constrained('helpers')
                ->onDelete('set null');
                
            $table->date('tanggal');
            $table->decimal('pemasukan', 15, 2);
            $table->string('remarks_pemasukan')->nullable();
            $table->decimal('pengeluaran', 15, 2);
            $table->string('remarks_pengeluaran')->nullable();
            $table->decimal('pemasukan_bersih', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    

    public function down(): void
    {
        Schema::dropIfExists('transaksi_jasas');
    }
};
