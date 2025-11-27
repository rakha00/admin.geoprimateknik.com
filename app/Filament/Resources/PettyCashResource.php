<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PettyCashResource\Pages;
use App\Filament\Resources\PettyCashResource\RelationManagers;
use App\Models\PettyCash;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;

class PettyCashResource extends Resource
{
    protected static ?string $model = PettyCash::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}

public static function form(Form $form): Form
{
    return $form
        ->schema([
            DatePicker::make('tanggal')
                ->required(),

            TextInput::make('nominal')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            Forms\Components\Select::make('kategori')
                ->options([
                    'Pemasukan' => 'Pemasukan',
                    'Pengeluaran' => 'Pengeluaran',
                ])
                ->required(),

            Forms\Components\Select::make('metode_pembayaran')
                ->options([
                    'Cash' => 'Cash',
                    'BCA' => 'BCA',
                    'Mandiri' => 'Mandiri',
                ])
                ->required(),

            Textarea::make('keterangan')
                ->rows(3),

            FileUpload::make('bukti_pembayaran')
                ->image()
                ->directory('bukti-pembayaran')
                ->imagePreviewHeight('500')
                ->downloadable()
                ->openable()
                ->nullable(),
        ]);
}


public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('tanggal')->date()->sortable(),
            TextColumn::make('kategori')->badge()
                ->colors([
                    'success' => fn ($state) => $state === 'Pemasukan',
                    'danger' => fn ($state) => $state === 'Pengeluaran',
                ]),
            TextColumn::make('nominal')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
            TextColumn::make('metode_pembayaran'),
            TextColumn::make('keterangan')->limit(30),
            ImageColumn::make('bukti_pembayaran')->height(100),
        ])
        ->filters([
            // filter bulan & rentang tanggal kayak sebelumnya
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPettyCashes::route('/'),
            'create' => Pages\CreatePettyCash::route('/create'),
            'edit' => Pages\EditPettyCash::route('/{record}/edit'),
        ];
    }
}
