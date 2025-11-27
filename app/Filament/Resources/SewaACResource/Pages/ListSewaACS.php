<?php

namespace App\Filament\Resources\SewaACResource\Pages;

use App\Filament\Resources\SewaACResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSewaACS extends ListRecords
{
    protected static string $resource = SewaACResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
