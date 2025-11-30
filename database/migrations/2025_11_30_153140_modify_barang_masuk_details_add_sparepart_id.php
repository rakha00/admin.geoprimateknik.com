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
        Schema::table('barang_masuk_details', function (Blueprint $table) {
            $table->foreignId('unit_ac_id')->nullable()->change();
            $table->foreignId('sparepart_id')->nullable()->after('unit_ac_id')->constrained('spareparts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barang_masuk_details', function (Blueprint $table) {
            $table->foreignId('unit_ac_id')->nullable(false)->change();
            $table->dropForeign(['sparepart_id']);
            $table->dropColumn('sparepart_id');
        });
    }
};
