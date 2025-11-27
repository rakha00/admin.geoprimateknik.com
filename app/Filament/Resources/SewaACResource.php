<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SewaACResource\Pages;
use App\Filament\Resources\SewaACResource\RelationManagers;
use App\Models\SewaAC;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, DatePicker};
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Request;

class SewaACResource extends Resource
{
    protected static ?string $model = SewaAC::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Sewa AC';
    protected static ?string $navigationGroup = 'Transaksi';

public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal')->required(),
                TextInput::make('pemasukan')->numeric()->default(0),
                TextInput::make('pengeluaran')->numeric()->default(0),
                TextInput::make('keterangan_pemasukan'),
                TextInput::make('keterangan_pengeluaran'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date()->sortable(),
                TextColumn::make('pemasukan')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('pengeluaran')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            ])
            ->filters([
                    \Filament\Tables\Filters\Filter::make('rentang_tanggal')
                    ->label('Rentang Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when($data['from'], fn($q, $from) => $q->whereDate('tanggal', '>=', $from))
                            ->when($data['until'], fn($q, $until) => $q->whereDate('tanggal', '<=', $until));
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
                    // Debug: Tampilkan struktur filter yang sebenarnya
                    $allFilters = $livewire->tableFilters ?? [];
                    
                    $bulanFilter = $allFilters['bulan'] ?? null;
                    $rentangFilter = $allFilters['rentang_tanggal'] ?? null;

                    
                    $processedFilters = [
                        'bulan' => is_array($bulanFilter) ? ($bulanFilter['value'] ?? null) : $bulanFilter,
                        'from' => is_array($rentangFilter) ? ($rentangFilter['from'] ?? null) : null,
                        'until' => is_array($rentangFilter) ? ($rentangFilter['until'] ?? null) : null,
                    ];

                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\SewaACExport($processedFilters),
                        'sewa_ac_filtered.xlsx'
                    );
                })
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSewaACS::route('/'),
            'create' => Pages\CreateSewaAC::route('/create'),
            'edit' => Pages\EditSewaAC::route('/{record}/edit'),
        ];
    }
}
