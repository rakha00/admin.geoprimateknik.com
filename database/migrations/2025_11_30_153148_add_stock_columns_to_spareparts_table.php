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
        Schema::table('spareparts', function (Blueprint $table) {
            $table->integer('stok_masuk')->default(0)->after('stock_awal');
            $table->integer('stok_keluar')->default(0)->after('stok_masuk');
            $table->integer('stok_akhir')->default(0)->after('stok_keluar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spareparts', function (Blueprint $table) {
            $table->dropColumn(['stok_masuk', 'stok_keluar', 'stok_akhir']);
        });
    }
};
