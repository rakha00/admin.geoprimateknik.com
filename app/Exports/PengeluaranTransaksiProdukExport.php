<?php

namespace App\Exports;

use App\Models\PengeluaranTransaksiProduk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PengeluaranTransaksiProdukExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = PengeluaranTransaksiProduk::query();

        if ($this->filters['bulan'] ?? false) {
            $query->whereMonth('tanggal', $this->filters['bulan']);
        }

        if ($this->filters['from'] ?? false) {
            $query->whereDate('tanggal', '>=', $this->filters['from']);
        }

        if ($this->filters['until'] ?? false) {
            $query->whereDate('tanggal', '<=', $this->filters['until']);
        }

        return $query->get()->map(function ($item) {
            return [
                'Tanggal' => Carbon::parse($item->tanggal)->format('d-m-Y'),
                'Pengeluaran' => $item->pengeluaran,
                'Keterangan Pengeluaran' => $item->keterangan_pengeluaran,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pengeluaran',
            'Keterangan Pengeluaran',
        ];
    }
}
