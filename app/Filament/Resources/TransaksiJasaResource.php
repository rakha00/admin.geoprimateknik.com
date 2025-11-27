<?php

namespace App\Filament\Resources;

use App\Models\TransaksiJasa;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\TransaksiJasaResource\Pages\ListTransaksiJasas;
use App\Filament\Resources\TransaksiJasaResource\Pages\CreateTransaksiJasa;
use App\Filament\Resources\TransaksiJasaResource\Pages\EditTransaksiJasa;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;


class TransaksiJasaResource extends Resource
{
    protected static ?string $model = TransaksiJasa::class;
    protected static ?string $navigationIcon  = 'heroicon-o-wrench';
    protected static ?string $navigationLabel = 'Transaksi Jasa';
    protected static ?string $navigationGroup = 'Transaksi';

public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}

public static function shouldRegisterNavigation(): bool
{
    return false;
}
    
public static function form(Form $form): Form
{
    return $form
        ->schema([
            DatePicker::make('tanggal')->label('Tanggal')->required(),

            Select::make('konsumen_jasa_id')
                ->label('Konsumen Jasa')
                ->relationship('konsumenJasa', 'nama')
                ->searchable()
                ->required(),

            Select::make('teknisi_id')
                ->label('Teknisi')
                ->relationship('teknisi', 'nama')
                ->searchable(),

            Select::make('helper_id')
                ->label('Helper')
                ->relationship('helper', 'nama')
                ->searchable(),

            Grid::make(2)->schema([
                TextInput::make('pemasukan')
                    ->label('Pemasukan')
                    ->numeric()
                    ->required(),
                Textarea::make('remarks_pemasukan')
                    ->label('Remarks Pemasukan')
                    ->rows(2),
            ]),

            Grid::make(2)->schema([
                TextInput::make('pengeluaran')
                    ->label('Pengeluaran')
                    ->numeric()
                    ->required(),
                Textarea::make('remarks_pengeluaran')
                    ->label('Remarks Pengeluaran')
                    ->rows(2),
            ]),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('konsumenJasa.nama')->label('Konsumen'),
            TextColumn::make('teknisi.nama')->label('Teknisi'),
            TextColumn::make('helper.nama')->label('Helper'),
            TextColumn::make('tanggal')->sortable()->label('Tanggal')->date(),
            TextColumn::make('pemasukan')->label('Pemasukan')
                ->formatStateUsing(fn ($state): string => number_format($state, 0, ',', '.')),
            TextColumn::make('remarks_pemasukan')->label('Remarks Pemasukan')->wrap(),
            TextColumn::make('pengeluaran')->label('Pengeluaran')
                ->formatStateUsing(fn ($state): string => number_format($state, 0, ',', '.')),
            TextColumn::make('remarks_pengeluaran')->label('Remarks Pengeluaran')->wrap(),
            TextColumn::make('pemasukan_bersih')->label('Pemasukan Bersih')
                ->formatStateUsing(fn ($state): string => number_format($state, 0, ',', '.')),
        ])
            ->actions([ EditAction::make() ])
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
            ->headerActions([  
                Action::make('export_excel')
                ->label('Download Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function ($action, $livewire) {
                    // Ambil filter yang sedang aktif langsung dari state Livewire-nya
                    $filters = $livewire->tableFilters ?? [];
        
                    return \Maatwebsite\Excel\Facades\Excel::download(
                        new \App\Exports\TransaksiJasaExport([
                            'bulan' => $filters['bulan']['value'] ?? null,
                            'from' => $filters['rentang_tanggal']['from'] ?? null,
                            'until' => $filters['rentang_tanggal']['until'] ?? null,
                        ]),
                        'transaksi_jasa.xlsx'
                    );
                })
            ])
            
            ->bulkActions([ DeleteBulkAction::make() ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTransaksiJasas::route('/'),
            'create' => CreateTransaksiJasa::route('/create'),
            'edit'   => EditTransaksiJasa::route('/{record}/edit'),
        ];
    }
}
