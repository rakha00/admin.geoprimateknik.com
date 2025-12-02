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
        Schema::table('pengeluaran_kantors', function (Blueprint $table) {
            $table->string('pembayaran')->nullable()->after('remarks'); // Cash / BCA / Mandiri
        });

        Schema::table('pengeluaran_transaksi_produks', function (Blueprint $table) {
            $table->string('pembayaran')->nullable()->after('keterangan_pengeluaran'); // Cash / BCA / Mandiri
        });

        Schema::table('sewa_a_c_s', function (Blueprint $table) {
            $table->string('pembayaran')->nullable()->after('keterangan_pengeluaran'); // Cash / BCA / Mandiri
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengeluaran_kantors', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
        });

        Schema::table('pengeluaran_transaksi_produks', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
        });

        Schema::table('sewa_a_c_s', function (Blueprint $table) {
            $table->dropColumn('pembayaran');
        });
    }
};
