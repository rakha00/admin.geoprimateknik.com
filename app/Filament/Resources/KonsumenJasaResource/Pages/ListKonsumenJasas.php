<?php

namespace App\Filament\Resources\KonsumenJasaResource\Pages;

use App\Filament\Resources\KonsumenJasaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKonsumenJasas extends ListRecords
{
    protected static string $resource = KonsumenJasaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
