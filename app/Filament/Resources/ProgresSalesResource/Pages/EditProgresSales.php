<?php

namespace App\Filament\Resources\ProgresSalesResource\Pages;

use App\Filament\Resources\ProgresSalesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgresSales extends EditRecord
{
    protected static string $resource = ProgresSalesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
