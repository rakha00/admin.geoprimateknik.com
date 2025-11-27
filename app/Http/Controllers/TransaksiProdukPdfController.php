<?php

namespace App\Http\Controllers;

use App\Models\TransaksiProduk;
use App\Models\Pajak;
use App\Models\NonPajak;
use Barryvdh\DomPDF\Facade\Pdf;

class TransaksiProdukPdfController extends Controller
{
    public function suratJalanSjt(NonPajak $transaksi)
    {
        $pdf = Pdf::loadView('pdf.SJT-surat-jalan', compact('transaksi'))
                  ->setPaper('a4','portrait');
        return $pdf->download('NP-Surat-Jalan-GTP-'.str_replace(['/','\\'],'-',$transaksi->no_surat_jalan).'.pdf');
    }

    public function suratJalanApjt(Pajak $transaksi)
    {
        $pdf = Pdf::loadView('pdf.APJT-surat-jalan', compact('transaksi'))
                  ->setPaper('a4','portrait');
        return $pdf->download('P-Surat-Jalan-GTP-'.str_replace(['/','\\'],'-',$transaksi->no_surat_jalan).'.pdf');
    }

    public function invoiceSjt(NonPajak $transaksi)
    {
        $pdf = Pdf::loadView('pdf.SJT-invoice', compact('transaksi'))
                  ->setPaper('a4','portrait');
        return $pdf->download('NP-Invoice-GTP-'.str_replace(['/','\\'],'-',$transaksi->no_invoice).'.pdf');
    }

    public function invoiceApjt(Pajak $transaksi)
    {
        $pdf = Pdf::loadView('pdf.APJT-invoice', compact('transaksi'))
                  ->setPaper('a4','portrait');
        return $pdf->download('P-Invoice-GTP-'.str_replace(['/','\\'],'-',$transaksi->no_invoice).'.pdf');
    }
}
