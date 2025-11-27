<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangMasukResource\Pages;
use App\Filament\Resources\BarangMasukResource\RelationManagers\BarangMasukDetailsRelationManager;
use App\Models\BarangMasuk;
use App\Models\PrincipleSubdealer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class BarangMasukResource extends Resource
{
    protected static ?string $model = BarangMasuk::class;

    protected static ?string $navigationIcon  = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel = 'Barang Masuk';
    protected static ?string $navigationGroup = 'Transaksi';

public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // GANTI relationship() dengan options() supaya langsung memuat data
                Forms\Components\Select::make('principle_subdealer_id')
                    ->label('Principle/Subdealer')
                    ->options(PrincipleSubdealer::pluck('nama', 'id'))
                    ->searchable()
                    ->required(),

                    Forms\Components\DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if (! $state) {
                            return;
                        }
                        $d     = Carbon::parse($state)->format('dmY');
                        $count = BarangMasuk::whereDate('tanggal', $state)->count() + 1;
                        $set('nomor_barang_masuk', "BM/{$d}-{$count}");
                    }),
    
                Forms\Components\TextInput::make('nomor_barang_masuk')
                    ->label('Nomor Barang Masuk')
                    ->readOnly()
                    ->reactive()
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
{
    $d     = Carbon::parse($data['tanggal'])->format('dmY');
    $count = BarangMasuk::whereDate('tanggal', $data['tanggal'])->count() + 1;
    $data['nomor_barang_masuk'] = "BM/{$d}-{$count}";
    return $data;
}

public static function mutateFormDataBeforeSave(array $data, BarangMasuk $record): array
{
    if (isset($data['tanggal'])) {
        $d     = Carbon::parse($data['tanggal'])->format('dmY');
        $count = BarangMasuk::whereDate('tanggal', $data['tanggal'])
                    ->where('id', '!=', $record->id)
                    ->count() + 1;
        $data['nomor_barang_masuk'] = "BM/{$d}-{$count}";
    }
    return $data;
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('principleSubdealer.nama')
                    ->label('Principle/Subdealer')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('nomor_barang_masuk')
                    ->label('No. Barang Masuk')
                    ->sortable(),
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
                \Filament\Tables\Actions\Action::make('export_excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($action, $livewire) {
                        $allFilters = $livewire->tableFilters ?? [];
            
                        $bulanFilter = $allFilters['bulan'] ?? null;
                        $rentangFilter = $allFilters['rentang_tanggal'] ?? null;
            
                        $filters = [
                            'bulan' => is_array($bulanFilter) ? ($bulanFilter['value'] ?? null) : $bulanFilter,
                            'from' => is_array($rentangFilter) ? ($rentangFilter['from'] ?? null) : null,
                            'until' => is_array($rentangFilter) ? ($rentangFilter['until'] ?? null) : null,
                        ];
            
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\BarangMasukExport($filters),
                            'barang_masuk_filtered.xlsx'
                        );
                    }),
            ])
            
            
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BarangMasukDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBarangMasuks::route('/'),
            'create' => Pages\CreateBarangMasuk::route('/create'),
            'edit'   => Pages\EditBarangMasuk::route('/{record}/edit'),
        ];
    }
}
