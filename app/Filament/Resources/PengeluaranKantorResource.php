<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengeluaranKantorResource\Pages;
use App\Filament\Resources\PengeluaranKantorResource\RelationManagers;
use App\Models\PengeluaranKantor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengeluaranKantorResource extends Resource
{
    protected static ?string $model = PengeluaranKantor::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Pengeluaran Kantor';

    public static function canViewAny(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('tanggal')
                    ->required(),
                Forms\Components\TextInput::make('pengeluaran')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('pembayaran')
                    ->options([
                        'Cash' => 'Cash',
                        'BCA' => 'BCA',
                        'Mandiri' => 'Mandiri',
                    ]),
                Forms\Components\Textarea::make('remarks')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pengeluaran')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pembayaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('remarks')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
                            new \App\Exports\PengeluaranKantorExport($processedFilters),
                            'pengeluaran_kantor_filtered.xlsx'
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
            'index' => Pages\ListPengeluaranKantors::route('/'),
            'create' => Pages\CreatePengeluaranKantor::route('/create'),
            'edit' => Pages\EditPengeluaranKantor::route('/{record}/edit'),
        ];
    }
}
