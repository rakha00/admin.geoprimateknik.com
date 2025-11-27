<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrincipleSubdealerResource\Pages;
use App\Models\PrincipleSubdealer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PrincipleSubdealerResource extends Resource
{
    protected static ?string $model = PrincipleSubdealer::class;

    protected static ?string $navigationIcon  = 'heroicon-o-squares-plus';
    protected static ?string $navigationLabel = 'Principle/Subdealer';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $recordTitleAttribute = 'nama';

public static function canViewAny(): bool
{
    return auth()->user()->level == 1;
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('sales')
                    ->label('Sales Principle/Subdealer')
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
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sales')
                    ->label('Sales')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
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
            // add RelationManagers here if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPrincipleSubdealers::route('/'),
            'create' => Pages\CreatePrincipleSubdealer::route('/create'),
            'edit'   => Pages\EditPrincipleSubdealer::route('/{record}/edit'),
        ];
    }
}
