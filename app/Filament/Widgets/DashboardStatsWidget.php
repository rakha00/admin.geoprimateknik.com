<?php

namespace App\Filament\Widgets;

use App\Models\SewaAC;
use App\Models\TransaksiJasa;
use App\Models\TransaksiProduk;
use App\Models\PengeluaranKantor;
use App\Models\PengeluaranTransaksiProduk;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\CanPoll; // Optional jika perlu auto-refresh
use Filament\Forms\Components\Select;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // ✅ Ini yang benar


class DashboardStatsWidget extends BaseWidget
{

    protected static ?int $sort = 1;


    // Set kolom menjadi 2 untuk layout grid
    protected int | string | array $columnSpan = 'full';
    
    use InteractsWithPageFilters;

public static function canView(): bool  // ← Bukan canViewAny()
{
    return auth()->user()?->level == 1;
}

    protected function getStats(): array
    {
        // Ambil nilai filter
        $bulan = $this->filters['bulan'] ?? date('m');
        $tahun = $this->filters['tahun'] ?? date('Y');

        return [
            // Baris pertama - Keuntungan
            $this->getKeuntunganProdukStat($bulan, $tahun),
            $this->getKeuntunganJasaStat($bulan, $tahun),
            
            // Baris kedua - Sewa AC dan Pengeluaran Kantor
            $this->getKeuntunganSewaACStat($bulan, $tahun),
            $this->getPengeluaranKantorStat($bulan, $tahun),
            
            // Baris ketiga - Pengeluaran Transaksi,
            $this->getTotalKeuntunganBersihStat($bulan, $tahun),
            $this->getTotalGajiSemuaKaryawanStat($bulan, $tahun),
        ];
    }
    
    protected function getColumns(): int
    {
        return 2; // Set 2 kolom untuk grid
    }
    

    private function getKeuntunganProdukStat($bulan, $tahun): Stat
    {
        $query = TransaksiProduk::query();
        
        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        $transaksis = $query->with('details')->get();
        $totalKeuntungan = $transaksis->sum(function ($transaksi) {
            return $transaksi->details->sum(function ($detail) {
                $modal = $detail->harga_modal * $detail->jumlah_keluar;
                $jual = $detail->harga_jual * $detail->jumlah_keluar;
                return $jual - $modal;
            });
        });

        return Stat::make('Keuntungan Produk', 'Rp ' . number_format($totalKeuntungan, 0, ',', '.'))
            ->description("Keuntungan bulan $bulan/$tahun")
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color('success');
    }

    private function getKeuntunganJasaStat($bulan, $tahun): Stat
    {
        $query = TransaksiJasa::query();
        
        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        $total = $query->get()->sum(function ($item) {
            return ($item->pemasukan ?? 0) - ($item->pengeluaran ?? 0);
        });

        return Stat::make('Keuntungan Jasa', 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Keuntungan bulan $bulan/$tahun")
            ->descriptionIcon('heroicon-m-wrench-screwdriver')
            ->color('success');
    }

    private function getKeuntunganSewaACStat($bulan, $tahun): Stat
    {
        $query = SewaAC::query();
        
        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        $total = $query->get()->sum(function ($item) {
            return ($item->pemasukan ?? 0) - ($item->pengeluaran ?? 0);
        });

        return Stat::make('Keuntungan Sewa AC', 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Bulan $bulan/$tahun")
            ->descriptionIcon('heroicon-m-cpu-chip')
            ->color('info');
    }

    private function getPengeluaranKantorStat($bulan, $tahun): Stat
    {
        $query = PengeluaranKantor::query();
        
        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        $total = $query->sum('pengeluaran');

        return Stat::make('Pengeluaran Kantor', 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Bulan $bulan/$tahun")
            ->descriptionIcon('heroicon-m-building-office')
            ->color('warning');
    }

    private function getPengeluaranTransaksiProdukStat($bulan, $tahun): Stat
    {
        $query = PengeluaranTransaksiProduk::query();
        
        if ($bulan) {
            $query->whereMonth('tanggal', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal', $tahun);
        }

        $total = $query->sum('pengeluaran');

    }

    private function getTotalGajiSemuaKaryawanStat($bulan, $tahun): Stat
{
    $models = [
        \App\Models\Sales::class,
        \App\Models\Staff::class,
        \App\Models\Teknisi::class,
        \App\Models\Helper::class,
        \App\Models\Gudang::class,
    ];

    $total = 0;

    foreach ($models as $model) {
        $records = $model::with(['penghasilanDetails' => function ($q) use ($bulan, $tahun) {
            if ($bulan) {
                $q->whereMonth('tanggal', $bulan);
            }
            if ($tahun) {
                $q->whereYear('tanggal', $tahun);
            }
        }])->get();

        foreach ($records as $record) {
            $penghasilan = $record->penghasilanDetails;

            $gajiPokok = $record->gaji_pokok ?? 0;
            $uangTransport = $record->uang_transport ?? 0; // hanya sales yang punya, jadi cek pakai null coalescing

            $lembur = $penghasilan->sum('lembur');
            $bonus = $penghasilan->sum('bonus') + $penghasilan->sum('bonus_retail') + $penghasilan->sum('bonus_projek');
            $kasbon = $penghasilan->sum('kasbon');

            $total += $gajiPokok + $uangTransport + $lembur + $bonus;
        }
    }

    return Stat::make('Total Gaji Semua Karyawan', 'Rp ' . number_format($total, 0, ',', '.'))
        ->description("Total gaji bulan $bulan/$tahun")
        ->descriptionIcon('heroicon-m-wallet')
        ->color('warning');
}


    private function getTotalKeuntunganBersihStat($bulan, $tahun): Stat
    {
        // Hitung total keuntungan bersih (semua pemasukan - semua pengeluaran)
        $keuntunganProduk = $this->hitungKeuntunganProduk($bulan, $tahun);
        $keuntunganJasa = $this->hitungKeuntunganJasa($bulan, $tahun);
        $keuntunganSewaAC = $this->hitungKeuntunganSewaAC($bulan, $tahun);
        $pengeluaranKantor = $this->hitungPengeluaranKantor($bulan, $tahun);
        $pengeluaranTransaksi = $this->hitungPengeluaranTransaksi($bulan, $tahun);
        

        $totalBersih = $keuntunganProduk + $keuntunganJasa + $keuntunganSewaAC - $pengeluaranKantor - $pengeluaranTransaksi;

        return Stat::make('Total Keuntungan Bersih', 'Rp ' . number_format($totalBersih, 0, ',', '.'))
            ->description("Total bersih bulan $bulan/$tahun")
            ->descriptionIcon('heroicon-m-banknotes')
            ->color($totalBersih >= 0 ? 'success' : 'danger');
    }

    // Helper methods untuk perhitungan
    private function hitungKeuntunganProduk($bulan, $tahun)
    {
        $query = TransaksiProduk::query();
        if ($bulan) $query->whereMonth('tanggal', $bulan);
        if ($tahun) $query->whereYear('tanggal', $tahun);
        
        return $query->with('details')->get()->sum(function ($transaksi) {
            return $transaksi->details->sum(function ($detail) {
                $modal = $detail->harga_modal * $detail->jumlah_keluar;
                $jual = $detail->harga_jual * $detail->jumlah_keluar;
                return $jual - $modal;
            });
        });
    }

    private function hitungKeuntunganJasa($bulan, $tahun)
    {
        $query = TransaksiJasa::query();
        if ($bulan) $query->whereMonth('tanggal', $bulan);
        if ($tahun) $query->whereYear('tanggal', $tahun);
        
        return $query->get()->sum(function ($item) {
            return ($item->pemasukan ?? 0) - ($item->pengeluaran ?? 0);
        });
    }

    private function hitungKeuntunganSewaAC($bulan, $tahun)
    {
        $query = SewaAC::query();
        if ($bulan) $query->whereMonth('tanggal', $bulan);
        if ($tahun) $query->whereYear('tanggal', $tahun);
        
        return $query->get()->sum(function ($item) {
            return ($item->pemasukan ?? 0) - ($item->pengeluaran ?? 0);
        });
    }

    private function hitungPengeluaranKantor($bulan, $tahun)
    {
        $query = PengeluaranKantor::query();
        if ($bulan) $query->whereMonth('tanggal', $bulan);
        if ($tahun) $query->whereYear('tanggal', $tahun);
        
        return $query->sum('pengeluaran');
    }

    private function hitungPengeluaranTransaksi($bulan, $tahun)
    {
        $query = PengeluaranTransaksiProduk::query();
        if ($bulan) $query->whereMonth('tanggal', $bulan);
        if ($tahun) $query->whereYear('tanggal', $tahun);
        
        return $query->sum('pengeluaran');
    }
}