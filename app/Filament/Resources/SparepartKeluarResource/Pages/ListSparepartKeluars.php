<?php

namespace App\Filament\Resources\SparepartKeluarResource\Pages;

use App\Filament\Resources\SparepartKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSparepartKeluars extends ListRecords
{
    protected static string $resource = SparepartKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
