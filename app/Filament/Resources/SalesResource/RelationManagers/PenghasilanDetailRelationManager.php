<?php

namespace App\Filament\Resources\SalesResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\{TextInput, DatePicker};
use Filament\Tables;
use Filament\Tables\Columns\{TextColumn, MoneyColumn};
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\Textarea;


class PenghasilanDetailRelationManager extends RelationManager
{
    protected static string $relationship = 'penghasilanDetails';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('kasbon')->numeric()->default(0),
            TextInput::make('lembur')->numeric()->default(0),
            TextInput::make('bonus_retail')->numeric()->default(0),
            TextInput::make('bonus_projek')->numeric()->default(0),
            Textarea::make('keterangan')->nullable(),
            DatePicker::make('tanggal')->required(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')->date(),
                TextColumn::make('kasbon')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('lembur')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('bonus_retail')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('bonus_projek')->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.')),
                TextColumn::make('keterangan'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
