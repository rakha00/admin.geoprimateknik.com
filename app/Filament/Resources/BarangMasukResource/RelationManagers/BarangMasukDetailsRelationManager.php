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
                Forms\Components\Radio::make('type')
                    ->label('Tipe Item')
                    ->options([
                        'unit_ac' => 'Unit AC',
                        'sparepart' => 'Sparepart',
                    ])
                    ->default('unit_ac')
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set) => $set('unit_ac_id', null) && $set('sparepart_id', null)),

                Forms\Components\Select::make('unit_ac_id')
                    ->label('Unit AC SKU')
                    ->options(UnitAc::pluck('sku', 'id'))
                    ->searchable()
                    ->reactive()
                    ->required(fn(Forms\Get $get) => $get('type') === 'unit_ac')
                    ->visible(fn(Forms\Get $get) => $get('type') === 'unit_ac')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $unit = UnitAc::find($state);
                        if ($unit) {
                            $set('sku', $unit->sku);
                            $set('nama_unit', $unit->nama_unit);
                            $set('harga_modal', $unit->harga_modal);
                            $set('harga_jual', $unit->harga_jual);
                        }
                    }),

                Forms\Components\Select::make('sparepart_id')
                    ->label('Sparepart SKU')
                    ->options(\App\Models\Sparepart::pluck('sku', 'id'))
                    ->searchable()
                    ->reactive()
                    ->required(fn(Forms\Get $get) => $get('type') === 'sparepart')
                    ->visible(fn(Forms\Get $get) => $get('type') === 'sparepart')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $sparepart = \App\Models\Sparepart::find($state);
                        if ($sparepart) {
                            $set('sku', $sparepart->sku);
                            $set('nama_unit', $sparepart->nama_sparepart);
                            $set('harga_modal', $sparepart->harga_modal);
                        }
                    }),

                Forms\Components\TextInput::make('nama_unit')
                    ->label('Nama Unit / Sparepart')
                    ->disabled()
                    ->reactive()
                    ->afterStateHydrated(function ($state, callable $set, $record) {
                        if ($record) {
                            if ($record->unitAc) {
                                $set('nama_unit', $record->unitAc->nama_unit);
                                $set('type', 'unit_ac');
                            } elseif ($record->sparepart) {
                                $set('nama_unit', $record->sparepart->nama_sparepart);
                                $set('type', 'sparepart');
                            }
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
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_unit')
                    ->label('Nama Unit / Sparepart')
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
                    ->getStateUsing(fn($record) => $record->harga_modal * $record->jumlah_barang_masuk)
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
