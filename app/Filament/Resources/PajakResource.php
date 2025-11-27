<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PajakResource\Pages\EditPajak;
use App\Filament\Resources\PajakResource\Pages\ListPajaks;
use App\Filament\Resources\PajakResource\Pages\CreatePajak;
use App\Filament\Resources\PajakResource\RelationManagers\PajakDetailRelationManager;
use App\Models\Pajak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Carbon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;

class PajakResource extends Resource
{
    protected static ?string $model = Pajak::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Transaksi Produk Pajak';
    protected static ?string $recordTitleAttribute = 'no_invoice';
    public static function getPluralLabel(): string
    {
        return 'Transaksi Produk Pajak'; // judul di list page
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            DatePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
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
                ->reactive()
                ->required()
                ->maxLength(50),

            TextInput::make('no_surat_jalan')
                ->label('No Surat Jalan')
                ->readOnly()
                ->reactive()
                ->required()
                ->maxLength(50),

            Select::make('sales_id')
                ->label('Sales')
                ->options(\App\Models\Sales::pluck('nama', 'id'))
                ->searchable()
                ->required(),

            Select::make('toko_id')
                ->label('Toko/Konsumen')
                ->options(\App\Models\Toko::pluck('nama_konsumen', 'id'))
                ->searchable()
                ->required(),

            Select::make('pembayaran')
                ->label('Pembayaran')
                ->options([
                    'Cash' => 'Cash',
                    'BCA' => 'BCA',
                    'Mandiri' => 'Mandiri',
                ])
                ->required(),

            Forms\Components\Textarea::make('remarks')
                ->label('Keterangan')
                ->rows(3)
                ->columnSpanFull(),
        ]);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $codes = self::generateCode($data['tanggal']);
        $data['no_invoice'] = $codes['invoice'];
        $data['no_surat_jalan'] = $codes['surat_jalan'];

        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data, Pajak $record): array
    {
        if (isset($data['tanggal'])) {
            $codes = self::generateCode($data['tanggal'], $record->id);
            $data['no_invoice'] = $codes['invoice'];
            $data['no_surat_jalan'] = $codes['surat_jalan'];
        }

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

        $query = Pajak::whereYear('tanggal', $year);
        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $lastNumber = $query->get()
            ->map(function ($item) use ($yy) {
                // Match GTP-PAJINV/YY/ROMAN/SEQ
                if (preg_match("/^GTP-PAJINV\/{$yy}\/[IVX]+\/(\d+)$/", $item->no_invoice, $matches)) {
                    return (int) $matches[1];
                }
                return 0;
            })
            ->max();

        $newNumber = $lastNumber + 1;
        $sequence = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return [
            'invoice' => "GTP-PAJINV/{$yy}/{$romanMonth}/{$sequence}",
            'surat_jalan' => "GTP-PAJSJ/{$yy}/{$romanMonth}/{$sequence}",
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_invoice')->label('Invoice')->sortable(),
                TextColumn::make('no_surat_jalan')->label('Surat Jalan')->sortable(),
                TextColumn::make('tanggal')->label('Tanggal')->date()->sortable(),
                TextColumn::make('sales.nama')->label('Sales')->sortable(),
                TextColumn::make('toko.nama_konsumen')->label('Toko/Konsumen')->sortable(),

                TextColumn::make('total_harga_jual')
                    ->label('Total Harga Jual')
                    ->getStateUsing(
                        fn(Pajak $record): int =>
                        $record->details->sum(function ($detail) {
                            return ($detail->harga_jual ?? 0) * ($detail->jumlah_keluar ?? 0);
                        })
                    )
                    ->formatStateUsing(
                        fn(int $state): string =>
                        number_format($state, 0, ',', '.')
                    ),

                TextColumn::make('total_keuntungan')
                    ->label('Total Keuntungan')
                    ->getStateUsing(
                        fn(Pajak $record): int =>
                        $record->details->sum(function ($detail) {
                            $totalJual = ($detail->harga_jual ?? 0) * ($detail->jumlah_keluar ?? 0);
                            $totalModal = ($detail->harga_modal ?? 0) * ($detail->jumlah_keluar ?? 0);
                            return $totalJual - $totalModal;
                        })
                    )
                    ->formatStateUsing(
                        fn(int $state): string =>
                        number_format($state, 0, ',', '.')
                    ),
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
                                // 'surat_jalan_sjt'  => 'Surat Jalan SJT',
                                'surat_jalan_apjt' => 'Surat Jalan',
                                // 'invoice_sjt'      => 'Invoice SJT',
                                'invoice_apjt' => 'Invoice',
                            ])
                            ->required(),
                    ])
                    ->action(fn(Pajak $record, array $data) => redirect()->to(
                        route(match ($data['type']) {
                            'surat_jalan_apjt' => 'transaksi-produk.surat-jalan.apjt',
                            'invoice_apjt' => 'transaksi-produk.invoice.apjt',
                        }, $record)
                    ))

            ])

            ->filters([
                \Filament\Tables\Filters\Filter::make('rentang_tanggal')
                    ->label('Rentang Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q, $from) => $q->whereDate('tanggal', '>=', $from))
                            ->when($data['until'], fn($q, $until) => $q->whereDate('tanggal', '<=', $until));
                    }),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            PajakDetailRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPajaks::route('/'),
            'create' => CreatePajak::route('/create'),
            'edit' => EditPajak::route('/{record}/edit'),
        ];
    }
}