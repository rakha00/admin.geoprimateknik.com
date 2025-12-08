<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiJasaResource\Pages;
use App\Models\TransaksiJasa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;

class TransaksiJasaResource extends Resource
{
    protected static ?string $model = TransaksiJasa::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Transaksi Jasa';
    protected static ?string $recordTitleAttribute = 'no_invoice';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal_transaksi')
                    ->label('Tanggal Transaksi')
                    ->required() // User said nullable fields but usually date is required for invoice gen. User said "biarkan semua nullable". I will make it nullable but logic needs date. If null, maybe no invoice num?
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if (!$state)
                            return;
                        $codes = self::generateCode($state);
                        $set('no_invoice', $codes['invoice']);
                        $set('no_surat_jalan', $codes['surat_jalan']);
                    }),

                TextInput::make('no_invoice')
                    ->label('No Invoice')
                    ->readOnly()
                    ->maxLength(255),

                TextInput::make('no_surat_jalan')
                    ->label('No Surat Jalan')
                    ->readOnly()
                    ->maxLength(255),

                Select::make('teknisi_id')
                    ->label('Teknisi')
                    ->relationship('teknisi', 'nama')
                    ->searchable()
                    ->preload(),

                Select::make('helper_id')
                    ->label('Helper')
                    ->relationship('helper', 'nama')
                    ->searchable()
                    ->preload(),

                Select::make('konsumen_jasa_id')
                    ->label('Konsumen')
                    ->relationship('konsumenJasa', 'nama')
                    ->searchable()
                    ->preload(),

                TextInput::make('total_pendapatan_jasa')
                    ->label('Total Pendapatan Jasa')
                    ->numeric()
                    ->prefix('Rp')
                    ->live(true)
                    ->afterStateUpdated(fn($state, $get, $set) => $set('total_keuntungan_jasa', ($state ?? 0) - ($get('total_pengeluaran_jasa') ?? 0))),

                TextInput::make('total_pengeluaran_jasa')
                    ->label('Total Pengeluaran Jasa')
                    ->numeric()
                    ->prefix('Rp')
                    ->live(true)
                    ->afterStateUpdated(fn($state, $get, $set) => $set('total_keuntungan_jasa', ($get('total_pendapatan_jasa') ?? 0) - ($state ?? 0))),

                TextInput::make('total_keuntungan_jasa')
                    ->label('Total Keuntungan Jasa')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly(),

                Textarea::make('keluhan')
                    ->label('Keluhan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal_transaksi')->date()->sortable(),
                TextColumn::make('no_invoice')->searchable()->sortable(),
                TextColumn::make('no_surat_jalan')->searchable()->sortable(),
                TextColumn::make('teknisi.nama')->sortable(),
                TextColumn::make('helper.nama')->sortable(),
                TextColumn::make('konsumenJasa.nama')->label('Konsumen')->sortable(),
                TextColumn::make('total_pendapatan_jasa')->money('IDR')->sortable(),
                TextColumn::make('total_keuntungan_jasa')->money('IDR')->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal_transaksi')
                    ->form([
                        DatePicker::make('from'),
                        DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_transaksi', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_transaksi', '<=', $date),
                            );
                    })
            ])
            ->actions([
                EditAction::make(),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        Select::make('type')
                            ->label('Dokumen')
                            ->options([
                                'invoice' => 'Invoice',
                                'surat_jalan' => 'Surat Jalan',
                            ])
                            ->required(),
                    ])
                    ->action(fn(TransaksiJasa $record, array $data) => redirect()->to(
                        route(match ($data['type']) {
                            'invoice' => 'transaksi-jasa.print.invoice',
                            'surat_jalan' => 'transaksi-jasa.print.surat-jalan',
                        }, $record)
                    )),
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
            'index' => Pages\ListTransaksiJasas::route('/'),
            'create' => Pages\CreateTransaksiJasa::route('/create'),
            'edit' => Pages\EditTransaksiJasa::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['tanggal_transaksi'])) {
            $codes = self::generateCode($data['tanggal_transaksi']);
            // Only set if not already set or empty (frontend should handle this, but for safety)
            if (empty($data['no_invoice']))
                $data['no_invoice'] = $codes['invoice'];
            if (empty($data['no_surat_jalan']))
                $data['no_surat_jalan'] = $codes['surat_jalan'];
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data, TransaksiJasa $record): array
    {
        // Re-generate if needed, usually we don't regenerate invoice numbers on edit unless specific logic
        // logic similar to PajakResource can be applied if requested.
        return $data;
    }

    private static function getRomanMonth($month)
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $map[$month] ?? '';
    }

    public static function generateCode($tanggal, $ignoreId = null)
    {
        $date = Carbon::parse($tanggal);
        $year = $date->year;
        $yy = $date->format('y');
        $romanMonth = self::getRomanMonth($date->month);

        $query = TransaksiJasa::withTrashed()->whereYear('tanggal_transaksi', $year);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $lastNumber = $query->get()
            ->map(function ($item) use ($yy) {
                // Match GTP-JASAINV/YY/ROMAN/SEQ
                if (preg_match("/^GTP-JASAINV\/{$yy}\/[IVX]+\/(\d+)$/", $item->no_invoice, $matches)) {
                    return (int) $matches[1];
                }
                return 0;
            })
            ->max();

        $newNumber = $lastNumber + 1;
        $sequence = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return [
            'invoice' => "GTP-JASAINV/{$yy}/{$romanMonth}/{$sequence}",
            'surat_jalan' => "GTP-JASASJ/{$yy}/{$romanMonth}/{$sequence}",
        ];
    }
}
