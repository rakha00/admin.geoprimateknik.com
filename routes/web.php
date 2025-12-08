<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransaksiProdukPdfController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/transaksi-produk/{transaksi}/surat-jalan/sjt', [TransaksiProdukPdfController::class, 'suratJalanSjt'])
    ->name('transaksi-produk.surat-jalan.sjt');

Route::get('/transaksi-produk/{transaksi}/surat-jalan/apjt', [TransaksiProdukPdfController::class, 'suratJalanApjt'])
    ->name('transaksi-produk.surat-jalan.apjt');

Route::get('/transaksi-produk/{transaksi}/invoice/sjt', [TransaksiProdukPdfController::class, 'invoiceSjt'])
    ->name('transaksi-produk.invoice.sjt');

Route::get('/transaksi-produk/{transaksi}/invoice/apjt', [TransaksiProdukPdfController::class, 'invoiceApjt'])
    ->name('transaksi-produk.invoice.apjt');

Route::get('/transaksi-jasa/{transaksi}/invoice', [\App\Http\Controllers\TransaksiJasaPdfController::class, 'invoice'])
    ->name('transaksi-jasa.print.invoice');

Route::get('/transaksi-jasa/{transaksi}/surat-jalan', [\App\Http\Controllers\TransaksiJasaPdfController::class, 'suratJalan'])
    ->name('transaksi-jasa.print.surat-jalan');