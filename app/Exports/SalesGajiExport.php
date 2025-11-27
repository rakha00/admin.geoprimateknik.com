<?php

namespace App\Exports;

use App\Models\Sales;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SalesGajiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Sales::with('penghasilanDetails');

        if ($this->filters['bulan'] ?? false) {
            $bulan = $this->filters['bulan'];
            $query->whereHas('penghasilanDetails', function ($q) use ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            });
        }

        return $query->get()->map(function ($sales) {
            $lembur = $sales->penghasilanDetails->sum('lembur');
            $bonusRetail = $sales->penghasilanDetails->sum('bonus_retail');
            $bonusProjek = $sales->penghasilanDetails->sum('bonus_projek');
            $kasbon = $sales->penghasilanDetails->sum('kasbon');
            $totalGaji = $sales->gaji_pokok + $sales->uang_transport + $bonusRetail + $bonusProjek;
            $gajiDiterima = $sales->gaji_pokok + $lembur + $bonusRetail + $bonusProjek - $kasbon;

            return [
                'Nama' => $sales->nama,
                'No HP' => $sales->no_hp,
                'Gaji Pokok' => $sales->gaji_pokok,
                'Uang Transport' => $sales->uang_transport,
                'Lembur' => $lembur,
                'Bonus Retail' => $bonusRetail,
                'Bonus Projek' => $bonusProjek,
                'Kasbon' => $kasbon,
                'Total Gaji' => $totalGaji,
                'Gaji Diterima' => $gajiDiterima,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Nama',
            'No HP',
            'Gaji Pokok',
            'Uang Transport',
            'Lembur',
            'Bonus Retail',
            'Bonus Projek',
            'Kasbon',
            'Total Gaji',
            'Gaji Diterima',
        ];
    }
}
