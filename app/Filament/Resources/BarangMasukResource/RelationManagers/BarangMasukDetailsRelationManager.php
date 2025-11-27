<?php

namespace App\Filament\Resources\BarangMasukResource\RelationManagers;

use App\Models\UnitAc;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BarangMasukDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $recordTitleAttribute = 'sku';

public function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('unit_ac_id')
                ->label('SKU')
                ->options(UnitAc::pluck('sku', 'id'))
                ->searchable()
                ->reactive()
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    $unit = UnitAc::find($state);
                    if ($unit) {
                        $set('sku',       $unit->sku);
                        $set('nama_unit', $unit->nama_unit);
                        $set('harga_modal', $unit->harga_modal);
                        $set('harga_jual',  $unit->harga_jual);
                    }
                }),

            Forms\Components\TextInput::make('nama_unit')
                ->label('Nama Unit')
                ->disabled()
                ->reactive()
                ->afterStateHydrated(function ($state, callable $set, $record) {
                    if ($record && $record->unitAc) {
                        $set('nama_unit', $record->unitAc->nama_unit);
                    }
                }),

            Forms\Components\TextInput::make('harga_modal')
                ->label('Harga Modal')
                ->prefix('Rp')
                ->disabled(), // view only

            Forms\Components\TextInput::make('jumlah_barang_masuk')
                ->label('Jumlah Barang Masuk')
                ->numeric()
                ->required(),

            Forms\Components\Textarea::make('remarks')
                ->label('Remarks')
                ->rows(3)
                ->columnSpanFull(),
        ]);
}


public function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('unitAc.sku')
                ->label('SKU')
                ->sortable(),

            Tables\Columns\TextColumn::make('unitAc.nama_unit')
                ->label('Nama Unit')
                ->sortable(),

            Tables\Columns\TextColumn::make('jumlah_barang_masuk')
                ->label('Jumlah')
                ->sortable(),

            Tables\Columns\TextColumn::make('harga_modal')
                ->label('Harga Modal')
                ->prefix('Rp ')
                ->sortable(),

            Tables\Columns\TextColumn::make('total_harga_modal')
                ->label('Total Harga Modal')
                ->getStateUsing(fn ($record) => $record->harga_modal * $record->jumlah_barang_masuk)
                ->prefix('Rp ')
                ->sortable(),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
}

}
