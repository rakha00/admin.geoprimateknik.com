<?php

namespace App\Exports;

use App\Models\SewaAC;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class SewaACExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = SewaAC::query();

        // Debug: uncomment untuk melihat filter yang diterima
        // dd('Filters received in Export:', $this->filters);

        if ($this->filters['bulan'] ?? false) {
            $query->whereMonth('tanggal', $this->filters['bulan']);
    
        }

        if ($this->filters['from'] ?? false) {
            $query->whereDate('tanggal', '>=', $this->filters['from']);
    
        }

        if ($this->filters['until'] ?? false) {
            $query->whereDate('tanggal', '<=', $this->filters['until']);
           
        }

        
        $results = $query->get();

        return $results->map(function ($item) {
            return [
                'Tanggal' => Carbon::parse($item->tanggal)->format('d-m-Y'),
                'Pemasukan' => $item->pemasukan,
                'Pengeluaran' => $item->pengeluaran,
                'Keterangan Pemasukan' => $item->keterangan_pemasukan,
                'Keterangan Pengeluaran' => $item->keterangan_pengeluaran,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pemasukan',
            'Pengeluaran',
            'Keterangan Pemasukan',
            'Keterangan Pengeluaran',
        ];
    }
}