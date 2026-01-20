<?php

namespace App\Exports;

use App\Models\PengeluaranKantor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PengeluaranKantorExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = PengeluaranKantor::query();

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
                'Keterangan' => $item->remarks,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pengeluaran',
            'Keterangan',
        ];
    }
}
