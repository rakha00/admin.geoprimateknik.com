<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitAcResource\Pages;
use App\Models\UnitAc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class UnitAcResource extends Resource
{
    protected static ?string $model = UnitAc::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Unit AC';

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

                TextColumn::make('stock_akhir')
                    ->label('Stock Akhir')
                    ->getStateUsing(fn (UnitAc $record): int => 
                        $record->stock_awal
                        + $record->barangMasukDetails()->sum('jumlah_barang_masuk')
                        - $record->transaksiProdukDetails()->sum('jumlah_keluar')
                    ),

                // 1) Stock Awal
                TextColumn::make('stock_awal')
                    ->label('Stock Awal')
                    ->visible(fn () => auth()->user()->level == 1),

                // 2) Stock Masuk = SUM(jumlah_barang_masuk)
                TextColumn::make('stock_masuk')
                    ->label('Stock Masuk')
                    ->visible(fn () => auth()->user()->level == 1)
                    ->getStateUsing(fn (UnitAc $record): int => 
                        $record->barangMasukDetails()->sum('jumlah_barang_masuk')
                    ),

                // 3) Stock Keluar = SUM(jumlah_keluar)
                TextColumn::make('stock_keluar')
                    ->label('Stock Keluar')
                    ->visible(fn () => auth()->user()->level == 1)
                    ->getStateUsing(fn (UnitAc $record): int => 
                        $record->transaksiProdukDetails()->sum('jumlah_keluar')
                    ),

                // 4) Stock Akhir = Awal + Masuk − Keluar
                TextColumn::make('stock_akhir')
                    ->label('Stock Akhir')
                    ->getStateUsing(fn (UnitAc $record): int => 
                        $record->stock_awal
                        + $record->barangMasukDetails()->sum('jumlah_barang_masuk')
                        - $record->transaksiProdukDetails()->sum('jumlah_keluar')
                    ),

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
            'index'  => Pages\ListUnitAcs::route('/'),
            'create' => Pages\CreateUnitAc::route('/create'),
            'edit'   => Pages\EditUnitAc::route('/{record}/edit'),
        ];
    }
}
