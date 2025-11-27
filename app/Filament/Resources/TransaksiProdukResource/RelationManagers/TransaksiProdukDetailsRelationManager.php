<?php

namespace App\Filament\Resources\TransaksiProdukResource\RelationManagers;

use App\Models\UnitAc;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Get;
use Filament\Forms\Set;

class TransaksiProdukDetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';
    protected static ?string $recordTitleAttribute = 'sku';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('sku')
                    ->label('SKU')
                    ->options(UnitAc::pluck('sku', 'sku')->toArray())
                    ->searchable()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function ($state, Set $set): void {
                        if (! $state) {
                            $set('nama_unit', '');
                            $set('harga_modal', 0);
                            return;
                        }

                        $unit = UnitAc::where('sku', $state)->first();
                        $set('nama_unit',   $unit->nama_unit   ?? '');
                        $set('harga_modal', $unit->harga_modal ?? 0);
                    }),

                TextInput::make('nama_unit')
                    ->label('Unit')
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('harga_modal')
                    ->label('Harga Modal')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('harga_jual')
                    ->label('Harga Jual')
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $this->calculateTotals($get, $set);
                    }),

                TextInput::make('jumlah_keluar')
                    ->label('Jumlah Keluar')
                    ->numeric()
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        $this->calculateTotals($get, $set);
                    }),

                // Hidden fields untuk menyimpan total ke database
                Forms\Components\Hidden::make('total_modal'),
                Forms\Components\Hidden::make('total_harga_jual'),

                Textarea::make('remarks')
                    ->label('Keterangan')
                    ->rows(3)
                    ->nullable(),
            ]);
    }

    /**
     * Helper method untuk menghitung dan set total
     */
    private function calculateTotals(Get $get, Set $set): void
    {
        $hargaModal = (float) ($get('harga_modal') ?? 0);
        $hargaJual = (float) ($get('harga_jual') ?? 0);
        $jumlahKeluar = (int) ($get('jumlah_keluar') ?? 0);

        $totalModal = $hargaModal * $jumlahKeluar;
        $totalHargaJual = $hargaJual * $jumlahKeluar;

        $set('total_modal', $totalModal);
        $set('total_harga_jual', $totalHargaJual);
    }

    /**
     * Pastikan total dihitung sebelum save
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // FIXED: Pastikan menggunakan float untuk perhitungan yang akurat
        $hargaModal = (float) ($data['harga_modal'] ?? 0);
        $hargaJual = (float) ($data['harga_jual'] ?? 0);
        $jumlahKeluar = (int) ($data['jumlah_keluar'] ?? 0);
        
        $data['total_modal'] = $hargaModal * $jumlahKeluar;
        $data['total_harga_jual'] = $hargaJual * $jumlahKeluar; // FIXED: Ini yang salah sebelumnya
       
        return $data;
    }

    /**
     * Pastikan total dihitung sebelum save saat edit
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // FIXED: Pastikan menggunakan float untuk perhitungan yang akurat
        $hargaModal = (float) ($data['harga_modal'] ?? 0);
        $hargaJual = (float) ($data['harga_jual'] ?? 0);
        $jumlahKeluar = (int) ($data['jumlah_keluar'] ?? 0);
        
        $data['total_modal'] = $hargaModal * $jumlahKeluar;
        $data['total_harga_jual'] = $hargaJual * $jumlahKeluar; // FIXED: Ini yang salah sebelumnya
       
        return $data;
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU'),

                TextColumn::make('nama_unit')
                    ->label('Unit'),

                TextColumn::make('jumlah_keluar')
                    ->label('Qty')
                    ->numeric(),

                TextColumn::make('harga_modal')
                    ->label('Harga Modal')
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),

                TextColumn::make('harga_jual')
                    ->label('Harga Jual')
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),

                // Menampilkan total dari database (jika ada) atau perhitungan manual
                TextColumn::make('total_modal')
                    ->label('Total Harga Modal')
                    ->getStateUsing(fn ($record) =>
                        $record->total_modal ?? ($record->harga_modal * $record->jumlah_keluar)
                    )
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),

                TextColumn::make('total_harga_jual')
                    ->label('Total Harga Jual')
                    ->getStateUsing(fn ($record) =>
                        $record->total_harga_jual ?? ($record->harga_jual * $record->jumlah_keluar)
                    )
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),

                TextColumn::make('keuntungan')
                    ->label('Keuntungan')
                    ->getStateUsing(function ($record) {
                        $totalJual = $record->total_harga_jual ?? ($record->harga_jual * $record->jumlah_keluar);
                        $totalModal = $record->total_modal ?? ($record->harga_modal * $record->jumlah_keluar);
                        return $totalJual - $totalModal;
                    })
                    ->formatStateUsing(fn (int $state): string => number_format($state, 0, ',', '.')),
                   
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}