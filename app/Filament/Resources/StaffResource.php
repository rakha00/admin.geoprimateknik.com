<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Filament\Resources\StaffResource\RelationManagers;
use App\Models\Staff;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Textarea, DatePicker};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Carbon;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
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
                    ->money('IDR', divideBy: 1, locale: 'id')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                // === Lembur ===
                TextColumn::make('lembur')
                    ->label('Lembur')
                    ->state(fn ($record, $livewire) => self::sumDetailFiltered($record, 'lembur', $livewire))
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                // === Bonus ===
                TextColumn::make('bonus')
                    ->label('Bonus')
                    ->state(fn ($record, $livewire) => self::sumDetailFiltered($record, 'bonus', $livewire))
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                // === Kasbon ===
                TextColumn::make('kasbon')
                    ->label('Kasbon')
                    ->state(fn ($record, $livewire) => self::sumDetailFiltered($record, 'kasbon', $livewire))
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                // === Total Gaji ===
                TextColumn::make('total_gaji')
                    ->label('Total Gaji')
                    ->state(function ($record, $livewire) {
                        $lembur = self::sumDetailFiltered($record, 'lembur', $livewire);
                        $bonus = self::sumDetailFiltered($record, 'bonus', $livewire);
                        return $record->gaji_pokok + $lembur + $bonus;
                    })
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                // === Gaji Diterima ===
                TextColumn::make('gaji_diterima')
                    ->label('Gaji Diterima')
                    ->state(function ($record, $livewire) {
                        $lembur = self::sumDetailFiltered($record, 'lembur', $livewire);
                        $bonus = self::sumDetailFiltered($record, 'bonus', $livewire);
                        $kasbon = self::sumDetailFiltered($record, 'kasbon', $livewire);
                        return $record->gaji_pokok + $lembur + $bonus - $kasbon;
                    })
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            ])
            ->filters([
                // Filter Rentang Tanggal
                Filter::make('tanggal')
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
                        $filters = $livewire->tableFilters ?? [];
                        $bulan = is_array($filters['bulan'] ?? null) ? ($filters['bulan']['value'] ?? null) : null;
                        $from = $filters['tanggal']['from'] ?? null;
                        $until = $filters['tanggal']['until'] ?? null;

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\StaffGajiExport([
                                'bulan' => $bulan,
                                'from' => $from,
                                'until' => $until,
                            ]),
                            'laporan_gaji_staff.xlsx'
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Helper untuk sum detail sesuai filter
     */
    private static function sumDetailFiltered($record, string $field, $livewire)
    {
        $filters = $livewire->tableFilters ?? [];
        $bulan = $filters['bulan']['value'] ?? null;
        $from = $filters['tanggal']['from'] ?? null;
        $until = $filters['tanggal']['until'] ?? null;

        return $record->penghasilanDetails
            ->filter(function ($detail) use ($bulan, $from, $until) {
                $tanggal = $detail->tanggal ? Carbon::parse($detail->tanggal) : null;

                return (!$bulan || ($tanggal && $tanggal->month == $bulan))
                    && (!$from || ($tanggal && $tanggal->gte(Carbon::parse($from))))
                    && (!$until || ($tanggal && $tanggal->lte(Carbon::parse($until))));
            })
            ->sum($field);
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
            'index' => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit' => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
