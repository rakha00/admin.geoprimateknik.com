<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GudangResource\Pages;
use App\Filament\Resources\GudangResource\RelationManagers;
use App\Models\Gudang;
use App\Models\DetailPenghasilanGudang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\{TextInput, Textarea};
use Filament\Tables\Columns\TextColumn;

class GudangResource extends Resource
{
    protected static ?string $model = Gudang::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'Gudang';
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->searchable(),
                TextColumn::make('no_hp'),

                TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('lembur')
                    ->label('Lembur')
                    ->state(fn ($record, $livewire) =>
                        $record->sumDetail('lembur', $livewire->tableFilters['bulan']['value'] ?? null, null)
                    )
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('bonus')
                    ->label('Bonus')
                    ->state(fn ($record, $livewire) =>
                        $record->sumDetail('bonus', $livewire->tableFilters['bulan']['value'] ?? null, null)
                    )
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('kasbon')
                    ->label('Kasbon')
                    ->state(fn ($record, $livewire) =>
                        $record->sumDetail('kasbon', $livewire->tableFilters['bulan']['value'] ?? null, null)
                    )
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                TextColumn::make('gaji_diterima')
                    ->label('Gaji Diterima')
                    ->state(fn ($record, $livewire) =>
                        $record->hitungGajiDiterima($livewire->tableFilters['bulan']['value'] ?? null, null)
                    )
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            ])

            ->filters([
                \Filament\Tables\Filters\Filter::make('rentang_tanggal')
                    ->label('Rentang Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        // return $query->when(
                        //     $data['from'],
                        //     fn ($q, $from) => $q->whereHas('penghasilanDetails', fn ($qq) => $qq->whereDate('tanggal', '>=', $from))
                        // )->when(
                        //     $data['until'],
                        //     fn ($q, $until) => $q->whereHas('penghasilanDetails', fn ($qq) => $qq->whereDate('tanggal', '<=', $until))
                        // );
                        return $query;
                    }),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
            ])

            ->headerActions([
                Tables\Actions\Action::make('export_excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($action, $livewire) {
                        $allFilters = $livewire->tableFilters ?? [];

                        $bulanFilter   = $allFilters['bulan'] ?? null;
                        $rentangFilter = $allFilters['rentang_tanggal'] ?? null;

                        $processedFilters = [
                            'bulan' => is_array($bulanFilter) ? ($bulanFilter['value'] ?? null) : $bulanFilter,
                            'from'  => is_array($rentangFilter) ? ($rentangFilter['from'] ?? null) : null,
                            'until' => is_array($rentangFilter) ? ($rentangFilter['until'] ?? null) : null,
                        ];

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\GudangGajiExport($processedFilters),
                            'laporan_gaji_gudang.xlsx'
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
            'index' => Pages\ListGudangs::route('/'),
            'create' => Pages\CreateGudang::route('/create'),
            'edit' => Pages\EditGudang::route('/{record}/edit'),
        ];
    }
}
