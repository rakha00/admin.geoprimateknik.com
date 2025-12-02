<?php

use App\Models\SparepartKeluar;
use App\Models\SparepartKeluarDetail;
use App\Models\Piutang;
use App\Models\Sales;
use App\Models\Toko;
use App\Models\Sparepart;

// Ensure we have dependencies
$sales = Sales::first();
if (!$sales) {
    $sales = Sales::create(['nama' => 'Test Sales', 'kode_sales' => 'S01', 'no_telp' => '08123']);
}
$toko = Toko::first();
if (!$toko) {
    $toko = Toko::create(['nama_konsumen' => 'Test Toko', 'alamat' => 'Test Address']);
}
$sparepart = Sparepart::first();
if (!$sparepart) {
    $sparepart = Sparepart::create(['nama_sparepart' => 'Test Part', 'sku' => 'TP01', 'harga_modal' => 10000, 'stok_awal' => 100]);
}

echo "Creating SparepartKeluar..." . PHP_EOL;
$sk = SparepartKeluar::create([
    'no_invoice' => 'TEST-INV-' . time(),
    'no_surat_jalan' => 'TEST-SJ-' . time(),
    'tanggal' => now(),
    'sales_id' => $sales->id,
    'toko_id' => $toko->id,
    'pembayaran' => 'Cash',
    'remarks' => 'Test Transaction',
]);
echo "Created SK ID: " . $sk->id . PHP_EOL;

echo "Creating Detail..." . PHP_EOL;
$detail = SparepartKeluarDetail::create([
    'sparepart_keluar_id' => $sk->id,
    'sparepart_id' => $sparepart->id,
    'sku' => $sparepart->sku,
    'nama_sparepart' => $sparepart->nama_sparepart,
    'harga_modal' => $sparepart->harga_modal,
    'harga_jual' => 20000,
    'jumlah_keluar' => 2,
    'total_harga_jual' => 40000,
    'keuntungan' => 20000,
]);
echo "Created Detail ID: " . $detail->id . PHP_EOL;

// Check Piutang
$p = Piutang::where('sparepart_keluar_id', $sk->id)->first();
if ($p) {
    echo "SUCCESS: Piutang created. ID: " . $p->id . " Total: " . $p->total_harga_modal . PHP_EOL;
} else {
    echo "FAILURE: Piutang NOT created." . PHP_EOL;
}
