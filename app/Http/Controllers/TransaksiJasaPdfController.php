<?php

namespace App\Http\Controllers;

use App\Models\TransaksiJasa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TransaksiJasaPdfController extends Controller
{
    public function invoice(TransaksiJasa $transaksi)
    {
        $pdf = Pdf::loadView('pdf.transaksi_jasa.invoice', compact('transaksi'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('Jasa-Invoice-' . str_replace(['/', '\\'], '-', $transaksi->no_invoice) . '.pdf');
    }

    public function suratJalan(TransaksiJasa $transaksi)
    {
        $pdf = Pdf::loadView('pdf.transaksi_jasa.surat-jalan', compact('transaksi'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('Jasa-Surat-Jalan-' . str_replace(['/', '\\'], '-', $transaksi->no_surat_jalan) . '.pdf');
    }
}
