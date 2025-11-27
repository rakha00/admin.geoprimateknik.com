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
        Schema::create('detail_penghasilan_teknisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teknisi_id')->constrained()->onDelete('cascade');
            $table->decimal('kasbon', 15, 2)->nullable();
            $table->decimal('lembur', 15, 2)->nullable();
            $table->decimal('bonus', 15, 2)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('remarks')->nullable();
            $table->date('tanggal');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penghasilan_teknisis');
    }
};
