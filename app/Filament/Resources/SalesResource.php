<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesResource\Pages;
use App\Filament\Resources\SalesResource\RelationManagers;
use App\Models\Sales;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Textarea, Repeater, DatePicker};
use Filament\Tables\Columns\TextColumn;

class SalesResource extends Resource
{
    protected static ?string $model = Sales::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Sales';
    protected static ?string $recordTitleAttribute = 'nama_sales';
    protected static ?string $navigationGroup = 'Karyawan';

    public static function canViewAny(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Sales')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('no_hp')
                    ->label('No. HP')
                    ->tel()
                    ->maxLength(20),

                Forms\Components\TextInput::make('alamat')
                    ->label('Alamat')
                    ->maxLength(255),

                TextInput::make('uang_transport')->required()->numeric(),

                TextInput::make('gaji_pokok')->required()->numeric(),


                Forms\Components\Textarea::make('keterangan')
                    ->label('Remarks')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'tidak aktif' => 'Tidak Aktif',
                    ])
                    ->default('aktif')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (\Filament\Forms\Set $set, $state) {
                        if ($state === 'tidak aktif') {
                            $set('terakhir_aktif', now()->format('Y-m-d'));
                        } else {
                            $set('terakhir_aktif', null);
                        }
                    }),
                Forms\Components\DatePicker::make('terakhir_aktif')
                    ->readOnly(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->label('Nama Sales')->searchable(),
                TextColumn::make('no_hp')->label('No. HP'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tidak aktif' => 'danger',
                    }),

                TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('uang_transport')
                    ->label('Uang Transport')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('lembur')
                    ->label('Lembur')
                    ->state(fn($record, $livewire) => $record->filterDetailSum('lembur', $livewire->tableFilters))
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('kasbon')
                    ->label('Kasbon')
                    ->state(fn($record, $livewire) => $record->filterDetailSum('kasbon', $livewire->tableFilters))
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('bonus_retail')
                    ->label('Bonus Retail')
                    ->state(fn($record, $livewire) => $record->filterDetailSum('bonus_retail', $livewire->tableFilters))
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('bonus_projek')
                    ->label('Bonus Projek')
                    ->state(fn($record, $livewire) => $record->filterDetailSum('bonus_projek', $livewire->tableFilters))
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('total_gaji')
                    ->label('Total Gaji')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->tableFilters;
                        $bonusRetail = $record->filterDetailSum('bonus_retail', $filters);
                        $bonusProjek = $record->filterDetailSum('bonus_projek', $filters);

                        return $record->gaji_pokok + $record->uang_transport + $bonusRetail + $bonusProjek;
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('gaji_diterima')
                    ->label('Gaji Diterima')
                    ->state(function ($record, $livewire) {
                        $filters = $livewire->tableFilters;

                        $lembur = $record->filterDetailSum('lembur', $filters);
                        $bonusRetail = $record->filterDetailSum('bonus_retail', $filters);
                        $bonusProjek = $record->filterDetailSum('bonus_projek', $filters);
                        $kasbon = $record->filterDetailSum('kasbon', $filters);

                        return $record->gaji_pokok + $record->uang_transport + $lembur + $bonusRetail + $bonusProjek - $kasbon;
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('terakhir_aktif')->date(),
            ])

            ->filters([
                \Filament\Tables\Filters\Filter::make('rentang_tanggal')
                    ->label('Rentang Tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ]),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
            ])

            ->headerActions([
                \Filament\Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($action, $livewire) {
                        $allFilters = $livewire->tableFilters ?? [];

                        $bulanFilter = $allFilters['bulan'] ?? null;
                        $rentangFilter = $allFilters['rentang_tanggal'] ?? null;

                        $processedFilters = [
                            'bulan' => is_array($bulanFilter) ? ($bulanFilter['value'] ?? null) : $bulanFilter,
                            'from' => is_array($rentangFilter) ? ($rentangFilter['from'] ?? null) : null,
                            'until' => is_array($rentangFilter) ? ($rentangFilter['until'] ?? null) : null,
                        ];

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\SalesGajiExport($processedFilters),
                            'laporan_gaji_sales.xlsx'
                        );
                    }),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            RelationManagers\PenghasilanDetailRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSales::route('/create'),
            'edit' => Pages\EditSales::route('/{record}/edit'),
        ];
    }
}
