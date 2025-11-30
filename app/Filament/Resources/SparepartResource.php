<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparepartResource\Pages;
use App\Models\Sparepart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class SparepartResource extends Resource
{
    protected static ?string $model = Sparepart::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Spareparts';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('sku')
                ->label('SKU')
                ->unique(ignoreRecord: true)
                ->required(),

            Forms\Components\TextInput::make('nama_sparepart')
                ->label('Nama Sparepart')
                ->required(),

            Forms\Components\TextInput::make('harga_modal')
                ->label('Harga Modal')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('satuan')
                ->label('Satuan (pcs, meter, dll)')
                ->required(),

            Forms\Components\TextInput::make('stock_awal')
                ->label('Stock Awal')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('sku')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('nama_sparepart')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('harga_modal')
                ->money('IDR', true),
            Tables\Columns\TextColumn::make('satuan'),
            Tables\Columns\TextColumn::make('stock_awal'),
            Tables\Columns\TextColumn::make('stok_masuk')->label('Stok Masuk'),
            Tables\Columns\TextColumn::make('stok_akhir')->label('Stok Akhir'),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSpareparts::route('/'),
            'create' => Pages\CreateSparepart::route('/create'),
            'edit' => Pages\EditSparepart::route('/{record}/edit'),
        ];
    }
}
