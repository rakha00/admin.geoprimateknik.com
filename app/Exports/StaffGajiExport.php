<?php

namespace App\Exports;

use App\Models\Staff;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StaffGajiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Staff::with('penghasilanDetails');

        if ($this->filters['bulan'] ?? false) {
            $bulan = $this->filters['bulan'];
            $query->whereHas('penghasilanDetails', function ($q) use ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            });
        }

        return $query->get()->map(function ($staff) {
            $lembur = $staff->penghasilanDetails->sum('lembur');
            $bonus = $staff->penghasilanDetails->sum('bonus');
            $kasbon = $staff->penghasilanDetails->sum('kasbon');
            $totalGaji = $staff->gaji_pokok + $lembur + $bonus;
            $gajiDiterima = $totalGaji - $kasbon;

            return [
                'Nama' => $staff->nama,
                'No HP' => $staff->no_hp,
                'Gaji Pokok' => $staff->gaji_pokok,
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
