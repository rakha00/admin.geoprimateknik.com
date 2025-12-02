<?php

namespace App\Filament\Resources\SparepartKeluarResource\RelationManagers;

use App\Models\Sparepart;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $recordTitleAttribute = 'nama_sparepart';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Select::make('sku')
                ->label('SKU / Sparepart')
                ->options(Sparepart::pluck('nama_sparepart', 'sku')->toArray()) // Or use SKU as key if unique
                ->searchable()
                ->live()
                ->required()
                ->afterStateUpdated(function ($state, Set $set) {
                    if (!$state) {
                        $set('nama_sparepart', '');
                        $set('sparepart_id', null);
                        $set('harga_patokan', 0);
                        return;
                    }

                    $sparepart = Sparepart::where('sku', $state)->first();
                    $set('nama_sparepart', $sparepart->nama_sparepart ?? '');
                    $set('sparepart_id', $sparepart->id ?? null);
                    $set('harga_patokan', $sparepart->harga_modal ?? 0);
                    $set('harga_modal', $sparepart->harga_modal ?? 0); // Auto-fill harga_modal
                }),

            TextInput::make('nama_sparepart')
                ->label('Nama Sparepart')
                ->disabled()
                ->dehydrated(),

            TextInput::make('harga_patokan')
                ->label('Harga Patokan')
                ->disabled()
                ->dehydrated(false),

            TextInput::make('harga_modal')
                ->label('Harga Modal')
                ->numeric()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $this->calculateTotals($get, $set);
                }),

            TextInput::make('harga_jual')
                ->label('Harga Jual')
                ->numeric()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $this->calculateTotals($get, $set);
                }),

            TextInput::make('jumlah_keluar')
                ->label('Jumlah Keluar')
                ->numeric()
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Get $get, Set $set) {
                    $this->calculateTotals($get, $set);
                }),

            Forms\Components\Hidden::make('total_harga_jual'),
            Forms\Components\Hidden::make('keuntungan'),
            Forms\Components\Hidden::make('sparepart_id'),

            Textarea::make('remarks')
                ->label('Keterangan')
                ->rows(2)
                ->nullable(),
        ]);
    }

    private function calculateTotals(Get $get, Set $set): void
    {
        $hargaModal = (float) ($get('harga_modal') ?? 0);
        $hargaJual = (float) ($get('harga_jual') ?? 0);
        $jumlah = (int) ($get('jumlah_keluar') ?? 0);

        $totalJual = $hargaJual * $jumlah;
        $totalModal = $hargaModal * $jumlah;
        $keuntungan = $totalJual - $totalModal;

        $set('total_harga_jual', $totalJual);
        $set('keuntungan', $keuntungan);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('nama_sparepart')->label('Sparepart'),
                TextColumn::make('jumlah_keluar')->label('Qty')->numeric(),
                TextColumn::make('harga_modal')
                    ->label('Harga Modal')
                    ->formatStateUsing(fn($state) => number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn($state) => number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('total_harga_jual')
                    ->label('Total Jual')
                    ->getStateUsing(fn($record) => $record->total_harga_jual ?? (($record->harga_jual ?? 0) * ($record->jumlah_keluar ?? 0)))
                    ->formatStateUsing(fn($state) => number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('keuntungan')
                    ->label('Keuntungan')
                    ->getStateUsing(fn($record) => ($record->total_harga_jual ?? (($record->harga_jual ?? 0) * ($record->jumlah_keluar ?? 0))) - (($record->harga_modal ?? 0) * ($record->jumlah_keluar ?? 0)))
                    ->formatStateUsing(fn($state) => number_format($state ?? 0, 0, ',', '.')),
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
