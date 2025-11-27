<?php

namespace App\Filament\Resources\UnitAcResource\Pages;

use App\Filament\Resources\UnitAcResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUnitAc extends EditRecord
{
    protected static string $resource = UnitAcResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
