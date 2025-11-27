<?php

namespace App\Filament\Resources\NonPajakResource\RelationManagers;

use App\Models\UnitAc;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class NonPajakDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Select::make('sku')
                ->label('SKU')
                ->options(UnitAc::pluck('sku', 'sku')->toArray())
                ->searchable()
                ->live()
                ->required()
                ->afterStateUpdated(function ($state, Set $set) {
                    if (! $state) {
                        $set('nama_unit', '');
                        $set('unit_ac_id', null);
                        $set('harga_patokan', 0);
                        return;
                    }

                    $unit = UnitAc::where('sku', $state)->first();
                    $set('nama_unit',   $unit->nama_unit ?? '');
                    $set('unit_ac_id',  $unit->id ?? null);
                    // harga_patokan hanya tampilan, ambil dari field referensi (misal harga_modal di UnitAc)
                    $set('harga_patokan', $unit->harga_modal ?? 0);
                }),

            TextInput::make('nama_unit')
                ->label('Unit')
                ->disabled()
                ->dehydrated(),

            TextInput::make('harga_patokan')
                ->label('Harga Patokan')
                ->disabled()
                ->dehydrated(false), // jangan simpan ke DB

            TextInput::make('harga_modal')
                ->label('Harga Modal (Manual)')
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

            Forms\Components\Hidden::make('total_modal'),
            Forms\Components\Hidden::make('total_harga_jual'),
            Forms\Components\Hidden::make('keuntungan'),

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

        $totalModal = $hargaModal * $jumlah;
        $totalJual = $hargaJual * $jumlah;
        $keuntungan = $totalJual - $totalModal;

        $set('total_modal', $totalModal);
        $set('total_harga_jual', $totalJual);
        $set('keuntungan', $keuntungan);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $hargaModal = (float) ($data['harga_modal'] ?? 0);
        $hargaJual  = (float) ($data['harga_jual'] ?? 0);
        $jumlah     = (int)   ($data['jumlah_keluar'] ?? 0);

        $data['total_modal'] = $hargaModal * $jumlah;
        $data['total_harga_jual'] = $hargaJual * $jumlah;
        $data['keuntungan'] = $data['total_harga_jual'] - $data['total_modal'];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // sama dengan before create
        return $this->mutateFormDataBeforeCreate($data);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')->label('SKU'),
                TextColumn::make('nama_unit')->label('Unit'),
                TextColumn::make('jumlah_keluar')->label('Qty'),
                TextColumn::make('harga_modal')
                    ->label('Harga Modal')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('total_modal')
                    ->label('Total Modal')
                    ->getStateUsing(fn ($record) => $record->total_modal ?? (($record->harga_modal ?? 0) * ($record->jumlah_keluar ?? 0)))
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),
                TextColumn::make('total_harga_jual')
                    ->label('Total Jual')
                    ->getStateUsing(fn ($record) => $record->total_harga_jual ?? (($record->harga_jual ?? 0) * ($record->jumlah_keluar ?? 0)))
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('keuntungan')
                    ->label('Keuntungan')
                    ->getStateUsing(fn ($record) => ($record->total_harga_jual ?? (($record->harga_jual ?? 0) * ($record->jumlah_keluar ?? 0))) - ($record->total_modal ?? (($record->harga_modal ?? 0) * ($record->jumlah_keluar ?? 0))))
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 0, ',', '.')),
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
