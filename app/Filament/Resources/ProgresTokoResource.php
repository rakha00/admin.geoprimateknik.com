<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgresTokoResource\Pages;
use App\Models\Toko;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Carbon;

class ProgresTokoResource extends Resource
{
    protected static ?string $model = Toko::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Progres Toko';
    protected static ?string $navigationGroup = 'Laporan';

    public static function shouldRegisterNavigation(): bool
{
    return false;
}

    public static function canViewAny(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_konsumen')
                    ->label('Nama Toko')
                    ->searchable(),

                TextColumn::make('total_penjualan')
                    ->label('Total Penjualan')
                    ->getStateUsing(function (Toko $record, $livewire) {
                        // Ambil filter dari table filters state
                        $month = $livewire->tableFilters['bulan']['value'] ?? null;
                        $year = $livewire->tableFilters['tahun']['value'] ?? null;
                        
                        $transactions = $record->transaksiProdukFix()
                            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
                            ->when($year, fn ($q) => $q->whereYear('created_at', $year))
                            ->with('details')
                            ->get();
                    
                        return $transactions->flatMap->details
                            ->sum(fn ($detail) => ($detail->harga_jual ?? 0) * ($detail->jumlah_keluar ?? 0));
                    })
                    ->money('IDR'),

                TextColumn::make('total_unit')
                    ->label('Total Unit Terjual')
                    ->getStateUsing(function (Toko $record, $livewire) {
                        $month = $livewire->tableFilters['bulan']['value'] ?? null;
                        $year = $livewire->tableFilters['tahun']['value'] ?? null;
                        
                        $transactions = $record->transaksiProdukFix()
                            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
                            ->when($year, fn ($q) => $q->whereYear('created_at', $year))
                            ->with('details')
                            ->get();
                        
                        return $transactions->flatMap->details
                            ->sum('jumlah_keluar');
                    }),

                TextColumn::make('total_keuntungan')
                    ->label('Total Keuntungan')
                    ->getStateUsing(function (Toko $record, $livewire) {
                        $month = $livewire->tableFilters['bulan']['value'] ?? null;
                        $year = $livewire->tableFilters['tahun']['value'] ?? null;
                        
                        $transactions = $record->transaksiProdukFix()
                            ->when($month, fn ($q) => $q->whereMonth('created_at', $month))
                            ->when($year, fn ($q) => $q->whereYear('created_at', $year))
                            ->with('details')
                            ->get();

                        $details = $transactions->flatMap->details;

                        $totalJual = $details->sum(fn ($d) => ($d->harga_jual ?? 0) * ($d->jumlah_keluar ?? 0));
                        $totalModal = $details->sum(fn ($d) => ($d->harga_modal ?? 0) * ($d->jumlah_keluar ?? 0));

                        return $totalJual - $totalModal;
                    })
                    ->money('IDR'),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Filter Tahun')
                    ->options(function () {
                        $years = range(now()->year, now()->year - 5);
                        return array_combine($years, $years);
                    })
                    ->placeholder('Semua Tahun')
                    ->query(function ($query, $data) {
                        // Filter ini tidak perlu query karena kita handle di getStateUsing
                        return $query;
                    }),

                SelectFilter::make('bulan')
                    ->label('Filter Bulan')
                    ->options([
                        '1' => 'Januari',
                        '2' => 'Februari',
                        '3' => 'Maret',
                        '4' => 'April',
                        '5' => 'Mei',
                        '6' => 'Juni',
                        '7' => 'Juli',
                        '8' => 'Agustus',
                        '9' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember',
                    ])
                    ->placeholder('Semua Bulan')
                    ->query(function ($query, $data) {
                        // Filter ini tidak perlu query karena kita handle di getStateUsing
                        return $query;
                    }),
            ])
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProgresTokos::route('/'),
        ];
    }
}