<?php

namespace App\Filament\Resources\NonPajakResource\Pages;

use App\Filament\Resources\NonPajakResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNonPajaks extends ListRecords
{
    protected static string $resource = NonPajakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
