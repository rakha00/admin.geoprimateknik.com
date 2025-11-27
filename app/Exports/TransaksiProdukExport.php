<?php

namespace App\Exports;

use App\Models\TransaksiProduk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Carbon;

class TransaksiProdukExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = TransaksiProduk::query();

        if ($this->filters['bulan'] ?? false) {
            $query->whereMonth('tanggal', $this->filters['bulan']);
        }

        if ($this->filters['from'] ?? false) {
            $query->whereDate('tanggal', '>=', $this->filters['from']);
        }

        if ($this->filters['until'] ?? false) {
            $query->whereDate('tanggal', '<=', $this->filters['until']);
        }

        return $query->with(['sales', 'toko'])->get()->map(function ($item) {
            $totalJual = $item->details->sum(fn ($d) => $d->harga_jual * $d->jumlah_keluar);
            $totalModal = $item->details->sum(fn ($d) => $d->harga_modal * $d->jumlah_keluar);
            return [
                'No Invoice'     => $item->no_invoice,
                'Surat Jalan'    => $item->no_surat_jalan,
                'Tanggal'        => Carbon::parse($item->tanggal)->format('d-m-Y'),
                'Sales'          => $item->sales->nama ?? '-',
                'Toko/Konsumen'  => $item->toko->nama_konsumen ?? '-',
                'Total Harga Jual'     => $totalJual,
                'Total Keuntungan' => $totalJual - $totalModal,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No Invoice',
            'Surat Jalan',
            'Tanggal',
            'Sales',
            'Toko/Konsumen',
            'Total Harga Jual',
            'Total Keuntungan',
        ];
    }
}
