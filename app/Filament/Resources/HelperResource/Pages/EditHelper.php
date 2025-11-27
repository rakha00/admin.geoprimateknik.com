<?php

namespace App\Filament\Resources\HelperResource\Pages;

use App\Filament\Resources\HelperResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHelper extends EditRecord
{
    protected static string $resource = HelperResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
