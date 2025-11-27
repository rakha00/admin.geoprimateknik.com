<?php

namespace App\Filament\Resources\UnitAcResource\Pages;

use App\Filament\Resources\UnitAcResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUnitAcs extends ListRecords
{
    protected static string $resource = UnitAcResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
