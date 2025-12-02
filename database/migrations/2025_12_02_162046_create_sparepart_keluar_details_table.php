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
        Schema::create('sparepart_keluar_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sparepart_keluar_id')->constrained('sparepart_keluars')->cascadeOnDelete();
            $table->foreignId('sparepart_id')->constrained('spareparts')->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->string('nama_sparepart');
            $table->decimal('harga_modal', 15, 2)->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->integer('jumlah_keluar')->default(0);
            $table->decimal('total_harga_jual', 15, 2)->default(0);
            $table->decimal('keuntungan', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sparepart_keluar_details');
    }
};
