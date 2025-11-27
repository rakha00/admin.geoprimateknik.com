<?php

namespace App\Exports;

use App\Models\BarangMasuk;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BarangMasukExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = BarangMasuk::with('principleSubdealer');

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
                'Nomor Barang Masuk' => $item->nomor_barang_masuk,
                'Tanggal' => Carbon::parse($item->tanggal)->format('d-m-Y'),
                'Principle/Subdealer' => optional($item->principleSubdealer)->nama,
                'Dibuat' => Carbon::parse($item->created_at)->format('d-m-Y H:i'),
                'Diubah' => Carbon::parse($item->updated_at)->format('d-m-Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nomor Barang Masuk',
            'Tanggal',
            'Principle/Subdealer',
            'Dibuat',
            'Diubah',
        ];
    }
}
