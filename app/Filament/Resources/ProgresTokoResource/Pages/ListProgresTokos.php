<?php

namespace App\Filament\Resources\ProgresTokoResource\Pages;

use App\Filament\Resources\ProgresTokoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProgresTokos extends ListRecords
{
    protected static string $resource = ProgresTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
