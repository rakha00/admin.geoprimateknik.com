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
        Schema::table('piutangs', function (Blueprint $table) {
            $table->foreignId('sparepart_keluar_id')->nullable()->constrained('sparepart_keluars')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->dropForeign(['sparepart_keluar_id']);
            $table->dropColumn('sparepart_keluar_id');
        });
    }
};
