<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiProdukResource\Pages\CreateTransaksiProduk;
use App\Filament\Resources\TransaksiProdukResource\Pages\EditTransaksiProduk;
use App\Filament\Resources\TransaksiProdukResource\Pages\ListTransaksiProduks;
use App\Filament\Resources\TransaksiProdukResource\RelationManagers\TransaksiProdukDetailsRelationManager;
use App\Models\TransaksiProduk;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\Action;

class TransaksiProdukResource extends Resource
{
    protected static ?string $model = TransaksiProduk::class;
    protected static ?string $navigationIcon  = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Transaksi Produk';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $recordTitleAttribute = 'no_invoice';

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
                DatePicker::make('tanggal')
                    ->label('Tanggal')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $get, $set) {
                        if (! $state) return;
                   
                        $tanggal = Carbon::parse($state);
                        $d       = $tanggal->format('dmY');
                   
                        $lastNumber = TransaksiProduk::whereDate('tanggal', $tanggal)
                        ->get()
                        ->map(function ($item) {
                            if (preg_match('/-(\d+)$/', $item->no_invoice, $matches)) {
                                return (int) $matches[1];
                            }
                            return 0;
                        })
                        ->max();
                   
                        $newNumber = $lastNumber + 1;
                   
                        $set('no_invoice',     "INV/{$d}-{$newNumber}");
                        $set('no_surat_jalan', "SJ/{$d}-{$newNumber}");
                    }),
                   

                TextInput::make('no_invoice')
                    ->label('No Invoice')
                    ->readOnly()     // readonly: masih dikirim di form submit
                    ->reactive()     // agar bisa diisi via afterStateUpdated
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
            ]);
    }

    /**
     * Pastikan sebelum create, kedua field ter‐set,
     * jadi INSERT akan selalu include no_invoice & no_surat_jalan.
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $tanggal = Carbon::parse($data['tanggal']);
        $d       = $tanggal->format('dmY');
   
        // Ambil semua nomor invoice pada tanggal tsb, lalu cari angka terbesar
        $lastNumber = TransaksiProduk::whereDate('tanggal', $tanggal)
            ->get()
            ->map(function ($item) {
                if (preg_match('/-(\d+)$/', $item->no_invoice, $matches)) {
                    return (int) $matches[1];
                }
                return 0;
            })
            ->max();
   
        $newNumber = $lastNumber + 1;
   
        $data['no_invoice']     = "INV/{$d}-{$newNumber}";
        $data['no_surat_jalan'] = "SJ/{$d}-{$newNumber}";
   
        return $data;
    }
   

    /**
     * Jika edit dan tanggal berubah, regen nomor.
     */
    public static function mutateFormDataBeforeSave(array $data, TransaksiProduk $record): array
    {
        if (isset($data['tanggal'])) {
            $d = Carbon::parse($data['tanggal'])->format('dmY');
            $count = TransaksiProduk::whereDate('tanggal', $data['tanggal'])
                        ->where('id', '!=', $record->id)
                        ->count() + 1;
            $data['no_invoice']     = "INV/{$d}-{$count}";
            $data['no_surat_jalan'] = "SJ/{$d}-{$count}";
        }

        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_invoice')->label('Invoice')->sortable(),
                TextColumn::make('no_surat_jalan')->label('Surat Jalan')->sortable(),
                TextColumn::make('tanggal')->label('Tanggal')->date()->sortable(),
                TextColumn::make('sales.nama')->label('Sales')->sortable()->searchable(),
                TextColumn::make('toko.nama_konsumen')->label('Toko/Konsumen')->sortable(),
               
                // FIXED: IKUTIN CARA PIUTANG - HANYA PERHITUNGAN MANUAL
                TextColumn::make('total_harga_jual')
                    ->label('Total Harga Jual')
                    ->getStateUsing(fn (TransaksiProduk $record): int =>
                        $record->details->sum(function ($detail) {
                            // SAMA SEPERTI DI PIUTANG: hanya perhitungan manual
                            return $detail->harga_jual * $detail->jumlah_keluar;
                        })
                    )
                    ->formatStateUsing(fn (int $state): string =>
                        number_format($state, 0, ',', '.')
                    ),
               
                // FIXED: IKUTIN CARA PIUTANG - HANYA PERHITUNGAN MANUAL
                TextColumn::make('total_keuntungan')
                    ->label('Total Keuntungan')
                    ->getStateUsing(fn (TransaksiProduk $record): int =>
                        $record->details->sum(function ($detail) {
                            // Hitung total jual manual
                            $totalJual = $detail->harga_jual * $detail->jumlah_keluar;
                           
                            // Hitung total modal manual
                            $totalModal = $detail->harga_modal * $detail->jumlah_keluar;
                           
                            return $totalJual - $totalModal;
                        })
                    )
                    ->formatStateUsing(fn (int $state): string =>
                        number_format($state, 0, ',', '.')
                    ),
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
                EditAction::make(),
   
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->form([
                        Select::make('type')
                            ->label('Dokumen')
                            ->options([
                                // 'surat_jalan_sjt'  => 'Surat Jalan SJT',
                                'surat_jalan_apjt' => 'Surat Jalan APJT',
                                // 'invoice_sjt'      => 'Invoice SJT',
                                'invoice_apjt'     => 'Invoice APJT',
                            ])
                            ->required(),
                    ])
                    ->action(fn(TransaksiProduk $record, array $data) => redirect()->to(
                        route(match($data['type']) {
                            'surat_jalan_sjt'  => 'transaksi-produk.surat-jalan.sjt',
                            'surat_jalan_apjt' => 'transaksi-produk.surat-jalan.apjt',
                            'invoice_sjt'      => 'transaksi-produk.invoice.sjt',
                            'invoice_apjt'     => 'transaksi-produk.invoice.apjt',
                        }, $record)
                    )),
                    \Filament\Tables\Actions\Action::make('export_excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function () {
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TransaksiJasaExport, // export class nanti kita bikin
                            'transaksi_jasa.xlsx'
                        );
                    }),      
            ])
            ->headerActions([
                \Filament\Tables\Actions\Action::make('Export Excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->requiresConfirmation()
                    ->action(function ($action, $livewire) {
                        $filters = $livewire->tableFilters ?? [];
            
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TransaksiProdukExport([
                                'bulan' => $filters['bulan']['value'] ?? null,
                                'from' => $filters['rentang_tanggal']['from'] ?? null,
                                'until' => $filters['rentang_tanggal']['until'] ?? null,
                            ]),
                            'transaksi-produk.xlsx'
                        );
                    }),
            ])
            
            ->bulkActions([DeleteBulkAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            TransaksiProdukDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTransaksiProduks::route('/'),
            'create' => CreateTransaksiProduk::route('/create'),
            'edit'   => EditTransaksiProduk::route('/{record}/edit'),
        ];
    }
}