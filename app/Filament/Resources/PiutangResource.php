<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PiutangResource\Pages;
use App\Models\Piutang;
use App\Models\Pajak;
use App\Models\NonPajak;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;

class PiutangResource extends Resource
{
    protected static ?string $model = Piutang::class;

    protected static ?string $navigationGroup = 'Reminder';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Piutang';

    public static function canViewAny(): bool
    {
        return auth()->user()->level == 1;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pajak_id')
                    ->label('Pajak')
                    ->options(Pajak::orderBy('no_invoice')->pluck('no_invoice', 'id'))
                    ->searchable()
                    ->nullable()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($state) {
                            $pajak = Pajak::with('details')->find($state);
                            $totalJual = $pajak->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
                            $set('total_harga_modal', $totalJual);
                        }
                    }),

                Forms\Components\Select::make('non_pajak_id')
                    ->label('Non Pajak')
                    ->options(NonPajak::orderBy('no_invoice_non_pajak')->pluck('no_invoice_non_pajak', 'id'))
                    ->searchable()
                    ->nullable()
                    ->reactive()
                    ->afterStateUpdated(function (Set $set, Get $get, $state) {
                        if ($state) {
                            $nonPajak = NonPajak::with('details')->find($state); // Tambahkan ->with('details')
                            $totalJual = $nonPajak->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
                            $set('total_harga_modal', $totalJual);
                        }
                    }),


                Forms\Components\DatePicker::make('due_date')
                    ->label('Jatuh Tempo')
                    ->nullable(),

                Forms\Components\TextInput::make('sudah_dibayar')
                    ->label('Sudah Dibayar')
                    ->numeric()
                    ->default(0),

                Forms\Components\Textarea::make('keterangan'),

                Forms\Components\Select::make('status_pembayaran')
                    ->options([
                        'belum lunas' => 'Belum Lunas',
                        'tercicil' => 'Tercicil',
                        'sudah lunas' => 'Sudah Lunas',
                    ])
                    ->required(),

                Forms\Components\FileUpload::make('fotos')
                    ->multiple()
                    ->preserveFilenames()
                    ->directory('piutang-foto')
                    ->openable()
                    ->downloadable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->getStateUsing(fn($record) => $record->pajak->tanggal ?? $record->nonPajak->tanggal ?? $record->sparepartKeluar->tanggal ?? $record->transaksiJasa->tanggal_transaksi ?? '-')
                    ->date(),

                TextColumn::make('invoice')
                    ->label('Invoice')
                    ->getStateUsing(
                        fn($record) =>
                        $record->pajak->no_invoice
                        ?? ($record->nonPajak ? $record->nonPajak->no_invoice_non_pajak : null)
                        ?? ($record->sparepartKeluar ? $record->sparepartKeluar->no_invoice : null)
                        ?? ($record->transaksiJasa ? $record->transaksiJasa->no_invoice : '-')
                    ),

                TextColumn::make('total_harga_modal')
                    ->label('Total Harga Jual')
                    ->money('IDR'),

                TextColumn::make('sudah_dibayar')
                    ->label('Sudah Dibayar')
                    ->money('IDR'),

                TextColumn::make('sisa_piutang')
                    ->label('Sisa Piutang')
                    ->getStateUsing(fn($record) => ($record->total_harga_modal ?? 0) - ($record->sudah_dibayar ?? 0))
                    ->money('IDR'),

                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date(),

                BadgeColumn::make('status_pembayaran')
                    ->colors([
                        'danger' => 'belum lunas',
                        'warning' => 'tercicil',
                        'success' => 'sudah lunas',
                    ])
                    ->label('Status'),

                TextColumn::make('keterangan'),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->with(['pajak.details', 'nonPajak.details', 'sparepartKeluar.details', 'transaksiJasa'])
            )
            ->filters([
                // Filter Bulan - DIPERBAIKI
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
                            return $query->where(function ($q) use ($data) {
                                $q->whereHas('pajak', function ($pq) use ($data) {
                                    $pq->whereMonth('tanggal', $data['value']);
                                })
                                    ->orWhereHas('nonPajak', function ($nq) use ($data) {
                                        $nq->whereMonth('tanggal', $data['value']);
                                    })
                                    ->orWhereHas('sparepartKeluar', function ($sq) use ($data) {
                                        $sq->whereMonth('tanggal', $data['value']);
                                    })
                                    ->orWhereHas('transaksiJasa', function ($tj) use ($data) {
                                        $tj->whereMonth('tanggal_transaksi', $data['value']);
                                    });
                            });
                        }
                        return $query;
                    }),
                // OPSIONAL: Filter Tahun juga
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
                            return $query->where(function ($q) use ($data) {
                                $q->whereHas('pajak', function ($pq) use ($data) {
                                    $pq->whereYear('tanggal', $data['value']);
                                })
                                    ->orWhereHas('nonPajak', function ($nq) use ($data) {
                                        $nq->whereYear('tanggal', $data['value']);
                                    })
                                    ->orWhereHas('sparepartKeluar', function ($sq) use ($data) {
                                        $sq->whereYear('tanggal', $data['value']);
                                    })
                                    ->orWhereHas('transaksiJasa', function ($tj) use ($data) {
                                        $tj->whereYear('tanggal_transaksi', $data['value']);
                                    });
                            });
                        }
                        return $query;
                    }),

                // Filter Tanggal Range - DIPERBAIKI
                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->where(function ($mainQuery) use ($data) {
                            $mainQuery->where(function ($subQuery) use ($data) {
                                // Filter untuk pajak
                                $subQuery->whereHas('pajak', function ($pajak) use ($data) {
                                    if ($data['from']) {
                                        $pajak->whereDate('tanggal', '>=', $data['from']);
                                    }
                                    if ($data['until']) {
                                        $pajak->whereDate('tanggal', '<=', $data['until']);
                                    }
                                });
                            })
                                ->orWhere(function ($subQuery) use ($data) {
                                    // Filter untuk non pajak
                                    $subQuery->whereHas('nonPajak', function ($nonPajak) use ($data) {
                                        if ($data['from']) {
                                            $nonPajak->whereDate('tanggal', '>=', $data['from']);
                                        }
                                        if ($data['until']) {
                                            $nonPajak->whereDate('tanggal', '<=', $data['until']);
                                        }
                                    });
                                })
                                ->orWhere(function ($subQuery) use ($data) {
                                    // Filter untuk sparepart keluar
                                    $subQuery->whereHas('sparepartKeluar', function ($sparepartKeluar) use ($data) {
                                        if ($data['from']) {
                                            $sparepartKeluar->whereDate('tanggal', '>=', $data['from']);
                                        }
                                        if ($data['until']) {
                                            $sparepartKeluar->whereDate('tanggal', '<=', $data['until']);
                                        }
                                    });
                                })
                                ->orWhere(function ($subQuery) use ($data) {
                                    // Filter untuk transaksi jasa
                                    $subQuery->whereHas('transaksiJasa', function ($transaksiJasa) use ($data) {
                                        if ($data['from']) {
                                            $transaksiJasa->whereDate('tanggal_transaksi', '>=', $data['from']);
                                        }
                                        if ($data['until']) {
                                            $transaksiJasa->whereDate('tanggal_transaksi', '<=', $data['until']);
                                        }
                                    });
                                });
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPiutangs::route('/'),
            'create' => Pages\CreatePiutang::route('/create'),
            'edit' => Pages\EditPiutang::route('/{record}/edit'),
        ];
    }
}
