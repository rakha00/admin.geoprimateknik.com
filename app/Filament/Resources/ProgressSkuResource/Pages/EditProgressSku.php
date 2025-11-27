<?php

namespace App\Filament\Resources\ProgressSkuResource\Pages;

use App\Filament\Resources\ProgressSkuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgressSku extends EditRecord
{
    protected static string $resource = ProgressSkuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
