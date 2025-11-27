<?php

namespace App\Exports;

use App\Models\Helper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class HelperGajiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Helper::with('penghasilanDetails');

        if ($this->filters['bulan'] ?? false) {
            $bulan = $this->filters['bulan'];
            $query->whereHas('penghasilanDetails', function ($q) use ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            });
        }

        return $query->get()->map(function ($helper) {
            $lembur = $helper->penghasilanDetails->sum('lembur');
            $bonus = $helper->penghasilanDetails->sum('bonus');
            $kasbon = $helper->penghasilanDetails->sum('kasbon');
            $totalGaji = $helper->gaji_pokok + $lembur + $bonus;
            $gajiDiterima = $totalGaji - $kasbon;

            return [
                'Nama' => $helper->nama,
                'No HP' => $helper->no_hp,
                'Gaji Pokok' => $helper->gaji_pokok,
                'Lembur' => $lembur,
                'Bonus' => $bonus,
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
            'Lembur',
            'Bonus',
            'Kasbon',
            'Total Gaji',
            'Gaji Diterima',
        ];
    }
}
