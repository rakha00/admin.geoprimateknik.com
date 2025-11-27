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
        Schema::create('sewa_a_c_s', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('pemasukan', 15, 2)->nullable();
            $table->decimal('pengeluaran', 15, 2)->nullable();
            $table->string('keterangan_pemasukan')->nullable();
            $table->string('keterangan_pengeluaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sewa_a_c_s');
    }
};
