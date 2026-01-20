<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitAcResource\Pages;
use App\Models\UnitAc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitAcResource extends Resource
{
    protected static ?string $model = UnitAc::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Unit AC';

    protected static ?string $navigationGroup = 'Inventori';

    protected static ?string $pluralModelLabel = 'Unit AC';

    protected static ?string $slug = 'unit-ac';

    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'unit AC';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->level == 1;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->level == 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                Forms\Components\TextInput::make('nama_unit')
                    ->label('Nama Unit')
                    ->required(),
                Forms\Components\TextInput::make('harga_modal')
                    ->label('Harga Modal')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('stock_awal')
                    ->label('Stock Awal')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')->label('SKU')
                    ->searchable(),
                TextColumn::make('nama_unit')->label('Nama Unit')
                    ->searchable(),
                TextColumn::make('harga_modal')
                    ->label('Harga Modal')
                    ->formatStateUsing(fn ($state): string => number_format($state, 0, ',', '.')),
                // 1) Stock Awal
                TextColumn::make('stock_awal')
                    ->label('Stock Awal')
                    ->visible(fn () => auth()->user()->level == 1),

                // 2) Stock Masuk = SUM(jumlah_barang_masuk)
                TextColumn::make('stock_masuk')
                    ->label('Stock Masuk')
                    ->visible(fn () => auth()->user()->level == 1)
                    ->getStateUsing(
                        fn (UnitAc $record): int => $record->barangMasukDetails()
                            ->whereHas('barangMasuk', function ($q) {
                                $q->where('status', 'Selesai');
                            })
                            ->sum('jumlah_barang_masuk')
                    ),

                // 3) Stock Keluar = SUM(jumlah_keluar)
                TextColumn::make('stok_keluar')
                    ->label('Stock Keluar')
                    ->visible(fn () => auth()->user()->level == 1)
                    ->getStateUsing(function (UnitAc $record) {
                        $keluarTransaksi = $record->transaksiProdukDetails()->sum('jumlah_keluar');
                        $keluarPajak = $record->pajakDetails()
                            ->whereHas('pajak', function ($q) {
                                $q->where('status', 'Selesai');
                            })
                            ->sum('jumlah_keluar');
                        $keluarNonPajak = $record->nonPajakDetails()
                            ->whereHas('nonPajak', function ($q) {
                                $q->where('status', 'Selesai');
                            })
                            ->sum('jumlah_keluar');

                        return $keluarTransaksi + $keluarPajak + $keluarNonPajak;
                    })
                    ->sortable(),

                // 4) Stock Akhir = Awal + Masuk − Keluar
                TextColumn::make('stok_akhir')
                    ->label('Stock Akhir')
                    ->visible(fn () => auth()->user()->level == 1)
                    ->getStateUsing(function (UnitAc $record) {
                        // Masuk
                        $masuk = $record->barangMasukDetails()
                            ->whereHas('barangMasuk', function ($q) {
                                $q->where('status', 'Selesai');
                            })
                            ->sum('jumlah_barang_masuk');

                        // Keluar
                        $keluarTransaksi = $record->transaksiProdukDetails()->sum('jumlah_keluar');
                        $keluarPajak = $record->pajakDetails()
                            ->whereHas('pajak', function ($q) {
                                $q->where('status', 'Selesai');
                            })
                            ->sum('jumlah_keluar');
                        $keluarNonPajak = $record->nonPajakDetails()
                            ->whereHas('nonPajak', function ($q) {
                                $q->where('status', 'Selesai');
                            })
                            ->sum('jumlah_keluar');
                        $keluar = $keluarTransaksi + $keluarPajak + $keluarNonPajak;

                        return ($record->stock_awal ?? 0) + $masuk - $keluar;
                    })
                    ->sortable(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()->level == 1),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnitAcs::route('/'),
            'create' => Pages\CreateUnitAc::route('/create'),
            'edit' => Pages\EditUnitAc::route('/{record}/edit'),
        ];
    }
}
