<?php

namespace App\Filament\Widgets;

use App\Models\SewaAC;
use App\Models\TransaksiJasa;
use App\Models\TransaksiProduk;
use App\Models\PengeluaranKantor;
use App\Models\PengeluaranTransaksiProduk;
use App\Models\Pajak;
use App\Models\NonPajak;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\CanPoll; // Optional jika perlu auto-refresh
use Filament\Forms\Components\Select;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // ✅ Ini yang benar
use Carbon\Carbon;


class DashboardStatsWidget extends BaseWidget
{

    protected static ?int $sort = 1;


    // Set kolom menjadi 2 untuk layout grid
    protected int|string|array $columnSpan = 'full';

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

            // Widget Metode Pembayaran - Net Income (Pemasukan - Pengeluaran)
            $this->getCashNetIncomeStat($bulan, $tahun),
            $this->getBCANetIncomeStat($bulan, $tahun),
            $this->getMandiriNetIncomeStat($bulan, $tahun),
        ];
    }

    protected function getColumns(): int
    {
        return 3; // Set 3 kolom untuk grid agar rapih (Cash, BCA, Mandiri)
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
            $query->whereMonth('tanggal_transaksi', $bulan);
        }
        if ($tahun) {
            $query->whereYear('tanggal_transaksi', $tahun);
        }

        $total = $query->get()->sum(function ($item) {
            return ($item->total_pendapatan_jasa ?? 0) - ($item->total_pengeluaran_jasa ?? 0);
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

        return Stat::make('Pengeluaran Transaksi Produk', 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Bulan $bulan/$tahun")
            ->descriptionIcon('heroicon-m-shopping-cart')
            ->color('warning');
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
            // Filter karyawan yang aktif atau yang terakhir_aktif masih di bulan/tahun yang sama atau setelahnya
            $query = $model::with([
                'penghasilanDetails' => function ($q) use ($bulan, $tahun) {
                    if ($bulan) {
                        $q->whereMonth('tanggal', $bulan);
                    }
                    if ($tahun) {
                        $q->whereYear('tanggal', $tahun);
                    }
                }
            ])->where(function ($q) use ($bulan, $tahun) {
                // Karyawan yang aktif
                $q->where('status', 'aktif')
                    // ATAU karyawan tidak aktif tapi terakhir_aktif di bulan/tahun filter atau setelahnya
                    ->orWhere(function ($subQ) use ($bulan, $tahun) {
                        $subQ->where('status', 'tidak aktif');

                        if ($bulan && $tahun) {
                            // Jika terakhir_aktif >= awal bulan filter, maka masih dihitung di bulan tersebut
                            $firstDayOfMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth();
                            $subQ->where('terakhir_aktif', '>=', $firstDayOfMonth);
                        }
                    });
            });

            $records = $query->get();

            foreach ($records as $record) {
                // Double check: jika tidak aktif dan terakhir_aktif sebelum bulan filter, skip
                if ($record->status === 'tidak aktif' && $record->terakhir_aktif) {
                    $terakhirAktif = Carbon::parse($record->terakhir_aktif);
                    $bulanFilter = Carbon::create($tahun, $bulan, 1);

                    // Jika terakhir aktif sebelum bulan filter, skip karyawan ini
                    if ($terakhirAktif->isBefore($bulanFilter->startOfMonth())) {
                        continue;
                    }
                }

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
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

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
        if ($bulan)
            $query->whereMonth('tanggal_transaksi', $bulan);
        if ($tahun)
            $query->whereYear('tanggal_transaksi', $tahun);

        return $query->get()->sum(function ($item) {
            return ($item->total_pendapatan_jasa ?? 0) - ($item->total_pengeluaran_jasa ?? 0);
        });
    }

    private function hitungKeuntunganSewaAC($bulan, $tahun)
    {
        $query = SewaAC::query();
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        return $query->get()->sum(function ($item) {
            return ($item->pemasukan ?? 0) - ($item->pengeluaran ?? 0);
        });
    }

    private function hitungPengeluaranKantor($bulan, $tahun)
    {
        $query = PengeluaranKantor::query();
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        return $query->sum('pengeluaran');
    }

    private function hitungPengeluaranTransaksi($bulan, $tahun)
    {
        $query = PengeluaranTransaksiProduk::query();
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        return $query->sum('pengeluaran');
    }

    // Widget Net Income per Metode Pembayaran
    private function getCashNetIncomeStat($bulan, $tahun): Stat
    {
        return $this->getNetIncomeStatByPayment($bulan, $tahun, 'Cash', 'success', 'heroicon-m-banknotes');
    }

    private function getBCANetIncomeStat($bulan, $tahun): Stat
    {
        return $this->getNetIncomeStatByPayment($bulan, $tahun, 'BCA', 'info', 'heroicon-m-credit-card');
    }

    private function getMandiriNetIncomeStat($bulan, $tahun): Stat
    {
        return $this->getNetIncomeStatByPayment($bulan, $tahun, 'Mandiri', 'warning', 'heroicon-m-credit-card');
    }

    private function getNetIncomeStatByPayment($bulan, $tahun, $paymentType, $color, $icon): Stat
    {
        // Hitung total pemasukan
        $pemasukan = 0;

        // Pajak
        $queryPajak = Pajak::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $queryPajak->whereMonth('tanggal', $bulan);
        if ($tahun)
            $queryPajak->whereYear('tanggal', $tahun);
        $pemasukan += $queryPajak->with('details')->get()->sum(function ($transaksi) {
            return $transaksi->details->sum('total_harga_jual');
        });

        // Non Pajak
        $queryNonPajak = NonPajak::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $queryNonPajak->whereMonth('tanggal', $bulan);
        if ($tahun)
            $queryNonPajak->whereYear('tanggal', $tahun);
        $pemasukan += $queryNonPajak->with('details')->get()->sum(function ($transaksi) {
            return $transaksi->details->sum('total_harga_jual');
        });

        // Sewa AC
        $querySewaAC = SewaAC::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $querySewaAC->whereMonth('tanggal', $bulan);
        if ($tahun)
            $querySewaAC->whereYear('tanggal', $tahun);
        $pemasukan += $querySewaAC->sum('pemasukan');

        // Hitung total pengeluaran
        $pengeluaran = 0;

        // Pengeluaran Transaksi Produk
        $queryPengTrans = PengeluaranTransaksiProduk::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $queryPengTrans->whereMonth('tanggal', $bulan);
        if ($tahun)
            $queryPengTrans->whereYear('tanggal', $tahun);
        $pengeluaran += $queryPengTrans->sum('pengeluaran');

        // Pengeluaran Kantor
        $queryPengKantor = PengeluaranKantor::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $queryPengKantor->whereMonth('tanggal', $bulan);
        if ($tahun)
            $queryPengKantor->whereYear('tanggal', $tahun);
        $pengeluaran += $queryPengKantor->sum('pengeluaran');

        // Hitung net income
        $netIncome = $pemasukan - $pengeluaran;

        // Format description
        $pemasukanFormatted = number_format($pemasukan, 0, ',', '.');
        $pengeluaranFormatted = number_format($pengeluaran, 0, ',', '.');

        return Stat::make("Net Income $paymentType", 'Rp ' . number_format($netIncome, 0, ',', '.'))
            ->description("Pemasukan: Rp $pemasukanFormatted | Pengeluaran: Rp $pengeluaranFormatted")
            ->descriptionIcon($icon)
            ->color($netIncome >= 0 ? $color : 'danger');
    }
}