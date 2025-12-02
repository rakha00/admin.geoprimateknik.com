<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengeluaranTransaksiProdukResource\Pages;
use App\Filament\Resources\PengeluaranTransaksiProdukResource\RelationManagers;
use App\Models\PengeluaranTransaksiProduk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\{TextInput, DatePicker};
use Filament\Tables\Columns\TextColumn;

class PengeluaranTransaksiProdukResource extends Resource
{
    protected static ?string $model = PengeluaranTransaksiProduk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
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
                TextInput::make('pengeluaran')->numeric()->default(0),
                Forms\Components\Select::make('pembayaran')
                    ->options([
                        'Cash' => 'Cash',
                        'BCA' => 'BCA',
                        'Mandiri' => 'Mandiri',
                    ]),
                TextInput::make('keterangan_pengeluaran'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date(),
                TextColumn::make('pengeluaran')->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('pembayaran')->sortable(),

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
                        $allFilters = $livewire->tableFilters ?? [];

                        $bulanFilter = $allFilters['bulan'] ?? null;
                        $rentangFilter = $allFilters['rentang_tanggal'] ?? null;

                        $processedFilters = [
                            'bulan' => is_array($bulanFilter) ? ($bulanFilter['value'] ?? null) : $bulanFilter,
                            'from' => is_array($rentangFilter) ? ($rentangFilter['from'] ?? null) : null,
                            'until' => is_array($rentangFilter) ? ($rentangFilter['until'] ?? null) : null,
                        ];

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\PengeluaranTransaksiProdukExport($processedFilters),
                            'pengeluaran_transaksi_produk_filtered.xlsx'
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengeluaranTransaksiProduks::route('/'),
            'create' => Pages\CreatePengeluaranTransaksiProduk::route('/create'),
            'edit' => Pages\EditPengeluaranTransaksiProduk::route('/{record}/edit'),
        ];
    }
}
