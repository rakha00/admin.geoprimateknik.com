<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang_masuk_details', function (Blueprint $table) {
            $table->string('sku')->nullable()->change();
            $table->string('nama_unit')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('barang_masuk_details', function (Blueprint $table) {
            $table->string('sku')->nullable(false)->change();
            $table->string('nama_unit')->nullable(false)->change();
        });
    }
};
