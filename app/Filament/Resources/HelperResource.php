<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HelperResource\Pages;
use App\Filament\Resources\HelperResource\RelationManagers;
use App\Models\Helper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\{TextInput, Textarea};
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;

class HelperResource extends Resource
{
    protected static ?string $model = Helper::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Helper';
    protected static ?string $navigationGroup = 'Karyawan';

    public static function canViewAny(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama')->required(),
                TextInput::make('no_hp')->required(),
                Textarea::make('alamat')->required(),
                TextInput::make('gaji_pokok')->required()->numeric(),
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
                TextColumn::make('nama')->searchable(),
                TextColumn::make('no_hp'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'aktif' => 'success',
                        'tidak aktif' => 'danger',
                    }),

                TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('lembur')
                    ->label('Lembur')
                    ->state(
                        fn($record, $livewire) =>
                        $record->sumDetail(
                            'lembur',
                            $livewire->tableFilters['bulan']['value'] ?? null,
                            $livewire->tableFilters['tanggal']['from'] ?? null,
                            $livewire->tableFilters['tanggal']['until'] ?? null
                        )
                    )
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('bonus')
                    ->label('Bonus')
                    ->state(
                        fn($record, $livewire) =>
                        $record->sumDetail(
                            'bonus',
                            $livewire->tableFilters['bulan']['value'] ?? null,
                            $livewire->tableFilters['tanggal']['from'] ?? null,
                            $livewire->tableFilters['tanggal']['until'] ?? null
                        )
                    )
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('kasbon')
                    ->label('Kasbon')
                    ->state(
                        fn($record, $livewire) =>
                        $record->sumDetail(
                            'kasbon',
                            $livewire->tableFilters['bulan']['value'] ?? null,
                            $livewire->tableFilters['tanggal']['from'] ?? null,
                            $livewire->tableFilters['tanggal']['until'] ?? null
                        )
                    )
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('gaji_diterima')
                    ->label('Gaji Diterima')
                    ->state(
                        fn($record, $livewire) =>
                        $record->hitungGajiDiterima(
                            $livewire->tableFilters['bulan']['value'] ?? null,
                            $livewire->tableFilters['tanggal']['from'] ?? null,
                            $livewire->tableFilters['tanggal']['until'] ?? null
                        )
                    )
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('terakhir_aktif')->date(),
            ])
            ->filters([

                \Filament\Tables\Filters\Filter::make('tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn($query, $data) => $query),
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
            'index' => Pages\ListHelpers::route('/'),
            'create' => Pages\CreateHelper::route('/create'),
            'edit' => Pages\EditHelper::route('/{record}/edit'),
        ];
    }
}
