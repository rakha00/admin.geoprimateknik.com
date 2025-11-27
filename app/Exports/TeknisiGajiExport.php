<?php

namespace App\Exports;

use App\Models\Teknisi;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TeknisiGajiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Teknisi::with('penghasilanDetails');

        if ($this->filters['bulan'] ?? false) {
            $bulan = $this->filters['bulan'];
            $query->whereHas('penghasilanDetails', function ($q) use ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            });
        }

        return $query->get()->map(function ($teknisi) {
            $lembur = $teknisi->penghasilanDetails->sum('lembur');
            $bonus = $teknisi->penghasilanDetails->sum('bonus');
            $kasbon = $teknisi->penghasilanDetails->sum('kasbon');
            $totalGaji = $teknisi->gaji_pokok + $lembur + $bonus;
            $gajiDiterima = $totalGaji - $kasbon;

            return [
                'Nama' => $teknisi->nama,
                'No HP' => $teknisi->no_hp,
                'Gaji Pokok' => $teknisi->gaji_pokok,
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