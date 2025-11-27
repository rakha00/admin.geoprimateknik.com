<?php

namespace App\Filament\Resources\ProgressMerkResource\Pages;

use App\Filament\Resources\ProgressMerkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgressMerk extends EditRecord
{
    protected static string $resource = ProgressMerkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
