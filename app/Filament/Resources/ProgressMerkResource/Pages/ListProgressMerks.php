<?php

namespace App\Filament\Resources\ProgressMerkResource\Pages;

use App\Filament\Resources\ProgressMerkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProgressMerks extends ListRecords
{
    protected static string $resource = ProgressMerkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
