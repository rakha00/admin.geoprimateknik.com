<?php

namespace App\Exports;

use App\Models\Gudang;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GudangGajiExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Gudang::with('penghasilanDetails');

        if ($this->filters['bulan'] ?? false) {
            $bulan = $this->filters['bulan'];
            $query->whereHas('penghasilanDetails', function ($q) use ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            });
        }

        return $query->get()->map(function ($gudang) {
            $lembur = $gudang->penghasilanDetails->sum('lembur');
            $bonus = $gudang->penghasilanDetails->sum('bonus');
            $kasbon = $gudang->penghasilanDetails->sum('kasbon');
            $totalGaji = $gudang->gaji_pokok + $lembur + $bonus;
            $gajiDiterima = $totalGaji - $kasbon;

            return [
                'Nama' => $gudang->nama,
                'No HP' => $gudang->no_hp,
                'Gaji Pokok' => $gudang->gaji_pokok,
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
