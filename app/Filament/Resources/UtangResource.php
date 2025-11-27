<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UtangResource\Pages;
use App\Filament\Resources\UtangResource\RelationManagers;
use App\Models\Utang;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\BarangMasuk;
use Filament\Forms\Get;
use Filament\Forms\Set;

class UtangResource extends Resource
{
    protected static ?string $model = Utang::class;

    protected static ?string $navigationGroup = 'Reminder';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Utang';
    
public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('barang_masuk_id')
                    ->label('Barang Masuk')
                    ->options(
                        BarangMasuk::with('principleSubdealer')
                            ->get()
                            ->mapWithKeys(fn ($bm) => [
                                $bm->id => $bm->nomor_barang_masuk . ' | ' .
                                    \Carbon\Carbon::parse($bm->tanggal)->format('d-m-Y') . ' - ' .
                                    $bm->principleSubdealer->nama
                            ])
                    )
                    ->searchable()
                    ->required()
                    ->live() // Ganti dari reactive() ke live()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($state) {
                            // Update total harga modal
                            $barangMasuk = BarangMasuk::with('barangMasukDetails.unitAc', 'principleSubdealer')->find($state);
                            
                            if ($barangMasuk) {
                                $totalHargaModal = $barangMasuk->barangMasukDetails->sum(function ($detail) {
                                    $harga = $detail->harga_modal ?? 0;
                                    $jumlah = $detail->jumlah_barang_masuk ?? 0;
                                    return $harga * $jumlah;
                                });
                                
                                $set('total_harga_modal_display', 'Rp ' . number_format($totalHargaModal, 0, ',', '.'));
                                $set('nama_principle_display', $barangMasuk->principleSubdealer->nama ?? '');
                            }
                        } else {
                            $set('total_harga_modal_display', '');
                            $set('nama_principle_display', '');
                        }
                    }),

                Forms\Components\TextInput::make('total_harga_modal_display')
                    ->label('Total Harga Modal')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, Get $get, $record = null) {
                        // Jika sedang edit (record tersedia), ambil dari field `total_harga_modal`
                        if ($record && $record->total_harga_modal !== null) {
                            return 'Rp ' . number_format($record->total_harga_modal, 0, ',', '.');
                        }

                        // Jika create (tidak ada record), hitung manual berdasarkan barang_masuk_id
                        $barangMasukId = $get('barang_masuk_id');
                        if (!$barangMasukId) return '';

                        $barangMasuk = \App\Models\BarangMasuk::with('barangMasukDetails.unitAc')->find($barangMasukId);
                        if (!$barangMasuk) return '';

                        $totalHargaModal = $barangMasuk->barangMasukDetails->sum(function ($detail) {
                            $hargaModal = $detail->unitAc->harga_modal ?? 0;
                            $jumlah = $detail->jumlah_barang_masuk ?? 0;
                            return $hargaModal * $jumlah;
                        });

                        return 'Rp ' . number_format($totalHargaModal, 0, ',', '.');
                    }),

                
                        
                Forms\Components\TextInput::make('nama_principle_display')
                    ->label('Nama Principle')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, Get $get, $record = null) {
                        $barangMasukId = $get('barang_masuk_id') ?? $record?->barang_masuk_id;
                        if (!$barangMasukId) return '';
                        
                        $barangMasuk = BarangMasuk::with('principleSubdealer')->find($barangMasukId);
                        return $barangMasuk?->principleSubdealer?->nama ?? '';
                    }),

                Forms\Components\DatePicker::make('due_date')
                    ->label('Jatuh Tempo')
                    ->required(),
                
                Forms\Components\TextInput::make('sudah_dibayar_sebelumnya')
                    ->label('Sudah Dibayar')
                    ->disabled()
                    ->dehydrated(false)
                    ->formatStateUsing(function ($state, $record = null) {
                        if (!$record) return 'Rp 0'; // Untuk create form
                        return 'Rp ' . number_format($record->sudah_dibayar ?? 0, 0, ',', '.');
                    }),

                Forms\Components\TextInput::make('pembayaran_baru')
                    ->label('Pembayaran Baru')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->live(onBlur: true) // Hanya update saat blur, bukan setiap keystroke
                    ->afterStateUpdated(function (Set $set, Get $get, $state, $record = null) {
                        $sudahDibayarLama = $record?->sudah_dibayar ?? 0;
                        $pembayaranBaru = (float) ($state ?? 0);
                        $totalBaru = $sudahDibayarLama + $pembayaranBaru;
                        
                        $set('sudah_dibayar', $totalBaru);
                        // Hapus update display karena sudah ada formatStateUsing

                        if ($state) {
                            $barangMasuk = BarangMasuk::with('barangMasukDetails.unitAc', 'principleSubdealer')->find($state);
                    
                            if ($barangMasuk) {
                                $totalHargaModal = $barangMasuk->barangMasukDetails->sum(function ($detail) {
                                    $harga = $detail->unitAc->harga_modal ?? 0;
                                    $jumlah = $detail->jumlah_barang_masuk ?? 0;
                                    return $harga * $jumlah;
                                });
                    
                                $set('total_harga_modal_display', 'Rp ' . number_format($totalHargaModal, 0, ',', '.'));
                                $set('nama_principle_display', $barangMasuk->principleSubdealer->nama ?? '');
                            }
                        } else {
                            $set('total_harga_modal_display', '');
                            $set('nama_principle_display', '');
                        }
                    }),

                Forms\Components\Hidden::make('sudah_dibayar')
                    ->default(function ($record) {
                        return $record?->sudah_dibayar ?? 0;
                    }),

                Forms\Components\Textarea::make('keterangan'),

                Forms\Components\Select::make('status_pembayaran')
                    ->options([
                        'belum lunas' => 'Belum Lunas',
                        'tercicil' => 'Tercicil',
                        'sudah lunas' => 'Sudah Lunas',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('fotos')
                    ->label('Foto Bukti')
                    ->multiple()
                    ->preserveFilenames()
                    ->reorderable()
                    ->directory('utang-foto')
                    ->openable()
                    ->downloadable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barangMasuk.nomor_barang_masuk')
                    ->label('No. Barang Masuk'),

                Tables\Columns\TextColumn::make('barangMasuk.tanggal')
                    ->label('Tanggal')
                    ->date(),

                Tables\Columns\TextColumn::make('total_harga_modal')
                    ->label('Total Harga Modal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                
                
                Tables\Columns\TextColumn::make('sudah_dibayar')
                    ->label('Sudah Dibayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date(),

                Tables\Columns\BadgeColumn::make('status_pembayaran')
                    ->colors([
                        'danger' => 'belum lunas',
                        'warning' => 'tercicil',
                        'success' => 'sudah lunas',
                    ])
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => ucwords($state)),
            ])
            ->modifyQueryUsing(fn (Builder $query) =>
                $query->with('barangMasuk.barangMasukDetails.unitAc')
            )
            ->filters([
    // Filter Bulan berdasarkan tanggal barang masuk
    \Filament\Tables\Filters\SelectFilter::make('bulan')
        ->label('Filter Bulan')
        ->options([
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ])
        ->query(function (Builder $query, array $data) {
            if (isset($data['value']) && $data['value']) {
                return $query->whereHas('barangMasuk', function ($q) use ($data) {
                    $q->whereMonth('tanggal', $data['value']);
                });
            }
            return $query;
        }),

    // Filter Tahun berdasarkan tanggal barang masuk
    \Filament\Tables\Filters\SelectFilter::make('tahun')
        ->label('Filter Tahun')
        ->options(function () {
            $years = [];
            $currentYear = date('Y');
            for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
                $years[$i] = $i;
            }
            return $years;
        })
        ->query(function (Builder $query, array $data) {
            if (isset($data['value']) && $data['value']) {
                return $query->whereHas('barangMasuk', function ($q) use ($data) {
                    $q->whereYear('tanggal', $data['value']);
                });
            }
            return $query;
        }),

    // Filter Rentang Tanggal
    Tables\Filters\Filter::make('tanggal')
        ->form([
            Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
            Forms\Components\DatePicker::make('until')->label('Sampai Tanggal'),
        ])
        ->query(function (Builder $query, array $data) {
            return $query->whereHas('barangMasuk', function ($q) use ($data) {
                if ($data['from']) {
                    $q->whereDate('tanggal', '>=', $data['from']);
                }
                if ($data['until']) {
                    $q->whereDate('tanggal', '<=', $data['until']);
                }
            });
        }),
])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUtangs::route('/'),
            'create' => Pages\CreateUtang::route('/create'),
            'edit' => Pages\EditUtang::route('/{record}/edit'),
        ];
    }
}