<?php

namespace App\Filament\Resources;

use App\Models\UnitAc;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ProgressSKUResource extends Resource
{
    protected static ?string $model = UnitAc::class;

    
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Progress SKU';
    protected static ?string $navigationGroup = 'Laporan';

    
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}

public static function shouldRegisterNavigation(): bool
{
    return false;
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                TextColumn::make('total_terjual')
                    ->label('Total Terjual')
                    ->getStateUsing(fn (UnitAc $record) =>
                        $record->transaksiProdukDetails->sum('jumlah_keluar')
                    )
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('bulan')
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
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if ($data['value']) {
                            return $query->whereHas('transaksiProdukDetails.transaksiProduk', function ($q) use ($data) {
                                $q->whereMonth('tanggal', $data['value']);
                            });
                        }
                        return $query;
                    }),
            ])
            
            ->paginated(false); // Biar semua tampil di satu halaman
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ProgressSkuResource\Pages\ListProgressSkus::route('/'),
        ];
    }
}
