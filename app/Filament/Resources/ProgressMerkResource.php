<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgressMerkResource\Pages;
use App\Models\TransaksiProdukDetail;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class ProgressMerkResource extends Resource
{
    // Tetap menggunakan model dummy, tapi data diambil dari custom logic
    protected static ?string $model = TransaksiProdukDetail::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Progress Merk';
    protected static ?string $navigationGroup = 'Laporan';

    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_unit')
                    ->label('Merk (Nama Unit)')
                    ->searchable()
                    ->getStateUsing(fn ($record) => $record->unitAc->nama_unit ?? 'N/A'),

                TextColumn::make('total_terjual')
                    ->label('Total Terjual')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        // Hitung total terjual untuk merk yang sama
                        return TransaksiProdukDetail::whereHas('unitAc', function ($query) use ($record) {
                            $query->where('nama_unit', $record->unitAc->nama_unit);
                        })->sum('jumlah_keluar');
                    }),

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
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->whereHas('transaksiProduk', function ($q) use ($data) {
                                $q->whereMonth('tanggal', $data['value']);
                            });
                        }
            
                        return $query;
                    }),
            ])
            
            
            ->query(function () {
                // Ambil satu record per nama_unit (yang pertama untuk setiap merk)
                $namaUnits = TransaksiProdukDetail::with('unitAc')
                    ->get()
                    ->pluck('unitAc.nama_unit')
                    ->unique()
                    ->filter();

                $ids = [];
                foreach ($namaUnits as $namaUnit) {
                    $firstRecord = TransaksiProdukDetail::whereHas('unitAc', function ($query) use ($namaUnit) {
                        $query->where('nama_unit', $namaUnit);
                    })->first();
                    
                    if ($firstRecord) {
                        $ids[] = $firstRecord->id;
                    }
                }

                return TransaksiProdukDetail::whereIn('id', $ids);
            })
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\ProgressMerkResource\Pages\ListProgressMerks::route('/'),
        ];
    }
}