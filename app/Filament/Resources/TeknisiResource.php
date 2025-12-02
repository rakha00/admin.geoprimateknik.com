<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeknisiResource\Pages;
use App\Filament\Resources\TeknisiResource\RelationManagers;
use App\Models\Teknisi;
use Filament\Forms\Form;
use Filament\Forms\Components\{TextInput, Textarea, DatePicker};
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Illuminate\Support\Carbon;
use Filament\Tables\Filters\SelectFilter;

class TeknisiResource extends Resource
{
    protected static ?string $model = Teknisi::class;
    protected static ?string $navigationGroup = 'Karyawan';
    protected static ?string $navigationLabel = 'Teknisi';
    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function canViewAny(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('nama')->required(),
            TextInput::make('no_hp')->required(),
            Textarea::make('alamat')->required(),
            TextInput::make('gaji_pokok')->required()->numeric(),
            \Filament\Forms\Components\Select::make('status')
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
            \Filament\Forms\Components\DatePicker::make('terakhir_aktif')
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
                    ->money('IDR', divideBy: 1, locale: 'id')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('lembur')
                    ->label('Lembur')
                    ->state(function ($record, $livewire) {
                        $from = $livewire->tableFilters['tanggal']['from'] ?? null;
                        $until = $livewire->tableFilters['tanggal']['until'] ?? null;

                        return $record->sumDetail('lembur', $from, $until);
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('bonus')
                    ->label('Bonus')
                    ->state(function ($record, $livewire) {
                        $from = $livewire->tableFilters['tanggal']['from'] ?? null;
                        $until = $livewire->tableFilters['tanggal']['until'] ?? null;

                        return $record->sumDetail('bonus', $from, $until);
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('kasbon')
                    ->label('Kasbon')
                    ->state(function ($record, $livewire) {
                        $from = $livewire->tableFilters['tanggal']['from'] ?? null;
                        $until = $livewire->tableFilters['tanggal']['until'] ?? null;

                        return $record->sumDetail('kasbon', $from, $until);
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('total_gaji')
                    ->label('Total Gaji')
                    ->state(function ($record, $livewire) {
                        $from = $livewire->tableFilters['tanggal']['from'] ?? null;
                        $until = $livewire->tableFilters['tanggal']['until'] ?? null;

                        $lembur = $record->sumDetail('lembur', $from, $until);
                        $bonus = $record->sumDetail('bonus', $from, $until);

                        return $record->gaji_pokok + $lembur + $bonus;
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('gaji_diterima')
                    ->label('Gaji Diterima')
                    ->state(function ($record, $livewire) {
                        $from = $livewire->tableFilters['tanggal']['from'] ?? null;
                        $until = $livewire->tableFilters['tanggal']['until'] ?? null;

                        return $record->hitungGajiDiterima($from, $until);
                    })
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('terakhir_aktif')->date(),
            ])
            ->filters([

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Dari: ' . Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Sampai: ' . Carbon::parse($data['until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('export_excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($action, $livewire) {
                        $filters = $livewire->tableFilters ?? [];
                        $from = $filters['tanggal']['from'] ?? null;
                        $until = $filters['tanggal']['until'] ?? null;

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TeknisiGajiExport(['from' => $from, 'until' => $until]),
                            'laporan_gaji_teknisi.xlsx'
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTeknisis::route('/'),
            'create' => Pages\CreateTeknisi::route('/create'),
            'edit' => Pages\EditTeknisi::route('/{record}/edit'),
        ];
    }
}
