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

            // Pajak Stats
            $this->getPajakCashStat($bulan, $tahun),
            $this->getPajakBCAStat($bulan, $tahun),
            $this->getPajakMandiriStat($bulan, $tahun),

            // Non Pajak Stats
            $this->getNonPajakCashStat($bulan, $tahun),
            $this->getNonPajakBCAStat($bulan, $tahun),
            $this->getNonPajakMandiriStat($bulan, $tahun),

            // Pengeluaran Kantor Stats
            $this->getPengeluaranKantorCashStat($bulan, $tahun),
            $this->getPengeluaranKantorBCAStat($bulan, $tahun),
            $this->getPengeluaranKantorMandiriStat($bulan, $tahun),

            // Pengeluaran Transaksi Produk Stats
            $this->getPengeluaranTransaksiProdukCashStat($bulan, $tahun),
            $this->getPengeluaranTransaksiProdukBCAStat($bulan, $tahun),
            $this->getPengeluaranTransaksiProdukMandiriStat($bulan, $tahun),

            // Sewa AC Stats
            $this->getSewaACCashStat($bulan, $tahun),
            $this->getSewaACBCAStat($bulan, $tahun),
            $this->getSewaACMandiriStat($bulan, $tahun),
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
            $records = $model::with([
                'penghasilanDetails' => function ($q) use ($bulan, $tahun) {
                    if ($bulan) {
                        $q->whereMonth('tanggal', $bulan);
                    }
                    if ($tahun) {
                        $q->whereYear('tanggal', $tahun);
                    }
                }
            ])->get();

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
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        return $query->get()->sum(function ($item) {
            return ($item->pemasukan ?? 0) - ($item->pengeluaran ?? 0);
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

    // Pajak Stats
    private function getPajakCashStat($bulan, $tahun): Stat
    {
        return $this->getPajakStatByPayment($bulan, $tahun, 'Cash', 'success', 'heroicon-m-banknotes');
    }

    private function getPajakBCAStat($bulan, $tahun): Stat
    {
        return $this->getPajakStatByPayment($bulan, $tahun, 'BCA', 'info', 'heroicon-m-credit-card');
    }

    private function getPajakMandiriStat($bulan, $tahun): Stat
    {
        return $this->getPajakStatByPayment($bulan, $tahun, 'Mandiri', 'warning', 'heroicon-m-credit-card');
    }

    private function getPajakStatByPayment($bulan, $tahun, $paymentType, $color, $icon): Stat
    {
        $query = Pajak::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        $total = $query->with('details')->get()->sum(function ($transaksi) {
            return $transaksi->details->sum('total_harga_jual');
        });

        return Stat::make("Pajak $paymentType", 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Total Pajak $paymentType $bulan/$tahun")
            ->descriptionIcon($icon)
            ->color($color);
    }

    // Non Pajak Stats
    private function getNonPajakCashStat($bulan, $tahun): Stat
    {
        return $this->getNonPajakStatByPayment($bulan, $tahun, 'Cash', 'success', 'heroicon-m-banknotes');
    }

    private function getNonPajakBCAStat($bulan, $tahun): Stat
    {
        return $this->getNonPajakStatByPayment($bulan, $tahun, 'BCA', 'info', 'heroicon-m-credit-card');
    }

    private function getNonPajakMandiriStat($bulan, $tahun): Stat
    {
        return $this->getNonPajakStatByPayment($bulan, $tahun, 'Mandiri', 'warning', 'heroicon-m-credit-card');
    }

    private function getNonPajakStatByPayment($bulan, $tahun, $paymentType, $color, $icon): Stat
    {
        $query = NonPajak::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        $total = $query->with('details')->get()->sum(function ($transaksi) {
            return $transaksi->details->sum('total_harga_jual');
        });

        return Stat::make("Non Pajak $paymentType", 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Total Non Pajak $paymentType $bulan/$tahun")
            ->descriptionIcon($icon)
            ->color($color);
    }

    // Pengeluaran Kantor Stats
    private function getPengeluaranKantorCashStat($bulan, $tahun): Stat
    {
        return $this->getPengeluaranKantorStatByPayment($bulan, $tahun, 'Cash', 'success', 'heroicon-m-banknotes');
    }

    private function getPengeluaranKantorBCAStat($bulan, $tahun): Stat
    {
        return $this->getPengeluaranKantorStatByPayment($bulan, $tahun, 'BCA', 'info', 'heroicon-m-credit-card');
    }

    private function getPengeluaranKantorMandiriStat($bulan, $tahun): Stat
    {
        return $this->getPengeluaranKantorStatByPayment($bulan, $tahun, 'Mandiri', 'warning', 'heroicon-m-credit-card');
    }

    private function getPengeluaranKantorStatByPayment($bulan, $tahun, $paymentType, $color, $icon): Stat
    {
        $query = PengeluaranKantor::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        $total = $query->sum('pengeluaran');

        return Stat::make("Peng. Kantor $paymentType", 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Total Peng. Kantor $paymentType $bulan/$tahun")
            ->descriptionIcon($icon)
            ->color($color);
    }

    // Pengeluaran Transaksi Produk Stats
    private function getPengeluaranTransaksiProdukCashStat($bulan, $tahun): Stat
    {
        return $this->getPengeluaranTransaksiProdukStatByPayment($bulan, $tahun, 'Cash', 'success', 'heroicon-m-banknotes');
    }

    private function getPengeluaranTransaksiProdukBCAStat($bulan, $tahun): Stat
    {
        return $this->getPengeluaranTransaksiProdukStatByPayment($bulan, $tahun, 'BCA', 'info', 'heroicon-m-credit-card');
    }

    private function getPengeluaranTransaksiProdukMandiriStat($bulan, $tahun): Stat
    {
        return $this->getPengeluaranTransaksiProdukStatByPayment($bulan, $tahun, 'Mandiri', 'warning', 'heroicon-m-credit-card');
    }

    private function getPengeluaranTransaksiProdukStatByPayment($bulan, $tahun, $paymentType, $color, $icon): Stat
    {
        $query = PengeluaranTransaksiProduk::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        $total = $query->sum('pengeluaran');

        return Stat::make("Peng. Transaksi $paymentType", 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Total Peng. Transaksi $paymentType $bulan/$tahun")
            ->descriptionIcon($icon)
            ->color($color);
    }

    // Sewa AC Stats
    private function getSewaACCashStat($bulan, $tahun): Stat
    {
        return $this->getSewaACStatByPayment($bulan, $tahun, 'Cash', 'success', 'heroicon-m-banknotes');
    }

    private function getSewaACBCAStat($bulan, $tahun): Stat
    {
        return $this->getSewaACStatByPayment($bulan, $tahun, 'BCA', 'info', 'heroicon-m-credit-card');
    }

    private function getSewaACMandiriStat($bulan, $tahun): Stat
    {
        return $this->getSewaACStatByPayment($bulan, $tahun, 'Mandiri', 'warning', 'heroicon-m-credit-card');
    }

    private function getSewaACStatByPayment($bulan, $tahun, $paymentType, $color, $icon): Stat
    {
        $query = SewaAC::query()->where('pembayaran', $paymentType);
        if ($bulan)
            $query->whereMonth('tanggal', $bulan);
        if ($tahun)
            $query->whereYear('tanggal', $tahun);

        $total = $query->sum('pemasukan'); // Asumsi yang dihitung pemasukan

        return Stat::make("Sewa AC $paymentType", 'Rp ' . number_format($total, 0, ',', '.'))
            ->description("Total Sewa AC $paymentType $bulan/$tahun")
            ->descriptionIcon($icon)
            ->color($color);
    }
}