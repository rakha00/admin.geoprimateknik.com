<?php

namespace App\Filament\Resources\PengeluaranKantorResource\Pages;

use App\Filament\Resources\PengeluaranKantorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengeluaranKantors extends ListRecords
{
    protected static string $resource = PengeluaranKantorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
