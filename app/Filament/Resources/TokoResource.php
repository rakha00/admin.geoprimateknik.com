<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TokoResource\Pages;
use App\Models\Toko;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;               // <-- Inilah yang pakai
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;

class TokoResource extends Resource
{
    protected static ?string $model = Toko::class;

    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Toko/Customer';

public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_konsumen')
                    ->label('Nama Konsumen')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('no_hp')
                    ->label('No. HP')
                    ->required()
                    ->maxLength(20),
                Forms\Components\TextInput::make('alamat')
                    ->label('Alamat')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_konsumen')->label('Nama'),
                TextColumn::make('no_hp')->label('No. HP'),
                TextColumn::make('alamat')->label('Alamat'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime(),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTokos::route('/'),
            'create' => Pages\CreateToko::route('/create'),
            'edit'   => Pages\EditToko::route('/{record}/edit'),
        ];
    }
}
