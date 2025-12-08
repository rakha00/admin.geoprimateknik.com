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
            $table->foreignId('transaksi_jasa_id')->nullable()->constrained('transaksi_jasas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('piutangs', function (Blueprint $table) {
            $table->dropForeign(['transaksi_jasa_id']);
            $table->dropColumn('transaksi_jasa_id');
        });
    }
};
