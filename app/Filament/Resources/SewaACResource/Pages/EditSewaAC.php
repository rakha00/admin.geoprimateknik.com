<?php

namespace App\Filament\Resources\SewaACResource\Pages;

use App\Filament\Resources\SewaACResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSewaAC extends EditRecord
{
    protected static string $resource = SewaACResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
