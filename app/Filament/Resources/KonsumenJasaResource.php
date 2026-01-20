<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KonsumenJasaResource\Pages;
use App\Models\KonsumenJasa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KonsumenJasaResource extends Resource
{
    protected static ?string $model = KonsumenJasa::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Konsumen Jasa';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $pluralModelLabel = 'Konsumen Jasa';

    protected static ?int $navigationSort = 1;

    protected static ?string $pluralLabel = 'Konsumen Jasa';

    protected static ?string $modelLabel = 'Konsumen Jasa';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('alamat')
                    ->label('Alamat')
                    ->maxLength(255),

                Forms\Components\TextInput::make('no_hp')
                    ->label('No HP')
                    ->tel()
                    ->maxLength(20),

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
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('alamat')
                    ->label('Alamat')
                    ->limit(30),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No HP'),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListKonsumenJasas::route('/'),
            'create' => Pages\CreateKonsumenJasa::route('/create'),
            'edit' => Pages\EditKonsumenJasa::route('/{record}/edit'),
        ];
    }
}
