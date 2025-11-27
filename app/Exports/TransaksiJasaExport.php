<?php

namespace App\Exports;

use App\Models\TransaksiJasa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransaksiJasaExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = TransaksiJasa::query();

        if (!empty($this->filters['bulan'])) {
            $query->whereMonth('tanggal', $this->filters['bulan']);
        }

        if (!empty($this->filters['from'])) {
            $query->whereDate('tanggal', '>=', $this->filters['from']);
        }

        if (!empty($this->filters['until'])) {
            $query->whereDate('tanggal', '<=', $this->filters['until']);
        }

        return $query->select('tanggal', 'pemasukan', 'remarks_pemasukan', 'pengeluaran', 'remarks_pengeluaran', 'pemasukan_bersih')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pemasukan',
            'Remarks Pemasukan',
            'Pengeluaran',
            'Remarks Pengeluaran',
            'Pemasukan Bersih',
        ];
    }
}