<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgresSalesResource\Pages;
use App\Models\Sales;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProgresSalesResource extends Resource
{
    protected static ?string $model = Sales::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Progres Sales';
    protected static ?string $navigationGroup = 'Laporan';

    // Static variable untuk menyimpan filter
    public static $currentFilters = [];

    public static function canViewAny(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Sales')
                    ->searchable(),

                TextColumn::make('total_penjualan')
                    ->label('Total Penjualan')
                    ->getStateUsing(function (Sales $record) {
                        $from = static::$currentFilters['from'] ?? null;
                        $until = static::$currentFilters['until'] ?? null;

                        $query = DB::table('transaksi_produk_details')
                            ->join('transaksi_produks', 'transaksi_produk_details.transaksi_produk_id', '=', 'transaksi_produks.id')
                            ->where('transaksi_produks.sales_id', $record->id)
                            ->selectRaw('SUM(transaksi_produk_details.harga_jual * transaksi_produk_details.jumlah_keluar) as total');

                        if ($from) {
                            $query->whereDate('transaksi_produks.tanggal', '>=', $from);
                        }
                        if ($until) {
                            $query->whereDate('transaksi_produks.tanggal', '<=', $until);
                        }

                        $result = $query->first();
                        return $result->total ?: 0;
                    })
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?: 0, 0, ',', '.')),

                TextColumn::make('total_unit')
                    ->label('Total Unit Terjual')
                    ->getStateUsing(function (Sales $record) {
                        $from = static::$currentFilters['from'] ?? null;
                        $until = static::$currentFilters['until'] ?? null;

                        $query = DB::table('transaksi_produk_details')
                            ->join('transaksi_produks', 'transaksi_produk_details.transaksi_produk_id', '=', 'transaksi_produks.id')
                            ->where('transaksi_produks.sales_id', $record->id)
                            ->selectRaw('SUM(transaksi_produk_details.jumlah_keluar) as total');

                        if ($from) {
                            $query->whereDate('transaksi_produks.tanggal', '>=', $from);
                        }
                        if ($until) {
                            $query->whereDate('transaksi_produks.tanggal', '<=', $until);
                        }

                        $result = $query->first();
                        return $result->total ?: 0;
                    }),

                TextColumn::make('rata_rata_keuntungan')
                    ->label('Rata-rata Keuntungan (%)')
                    ->getStateUsing(function (Sales $record) {
                        $from = static::$currentFilters['from'] ?? null;
                        $until = static::$currentFilters['until'] ?? null;

                        $query = DB::table('transaksi_produk_details')
                            ->join('transaksi_produks', 'transaksi_produk_details.transaksi_produk_id', '=', 'transaksi_produks.id')
                            ->where('transaksi_produks.sales_id', $record->id)
                            ->selectRaw('
                                SUM(transaksi_produk_details.harga_modal * transaksi_produk_details.jumlah_keluar) as total_modal,
                                SUM((transaksi_produk_details.harga_jual - transaksi_produk_details.harga_modal) * transaksi_produk_details.jumlah_keluar) as total_keuntungan
                            ');

                        if ($from) {
                            $query->whereDate('transaksi_produks.tanggal', '>=', $from);
                        }
                        if ($until) {
                            $query->whereDate('transaksi_produks.tanggal', '<=', $until);
                        }

                        $result = $query->first();
                        
                        if (!$result || $result->total_modal == 0) {
                            return '0%';
                        }

                        $percentage = round(($result->total_keuntungan / $result->total_modal) * 100, 2);
                        return $percentage . '%';
                    }),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->placeholder('Pilih tanggal mulai'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->placeholder('Pilih tanggal akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // Simpan filter ke static variable
                        static::$currentFilters = $data;
                        
                        return $query->when(
                            $data['from'] || $data['until'],
                            function (Builder $query) use ($data) {
                                // Hanya tampilkan Sales yang punya transaksi dalam rentang tanggal
                                $query->whereHas('transaksiProduks', function ($q) use ($data) {
                                    if ($data['from']) {
                                        $q->whereDate('tanggal', '>=', $data['from']);
                                    }
                                    if ($data['until']) {
                                        $q->whereDate('tanggal', '<=', $data['until']);
                                    }
                                });
                            }
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from']) {
                            $indicators['from'] = 'Dari: ' . Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until']) {
                            $indicators['until'] = 'Sampai: ' . Carbon::parse($data['until'])->format('d M Y');
                        }

                        return $indicators;
                    }),
            ])
            ->emptyStateHeading('Tidak ada data sales')
            ->emptyStateDescription('Belum ada data sales pada periode yang dipilih atau belum ada sales yang terdaftar.')
            ->emptyStateIcon('heroicon-o-chart-bar-square')
            ->striped()
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProgresSales::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getModelLabel(): string
    {
        return 'Progres Sales';
    }
}