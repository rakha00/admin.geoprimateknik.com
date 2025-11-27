<?php

namespace App\Filament\Resources\ProgresTokoResource\Pages;

use App\Filament\Resources\ProgresTokoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProgresToko extends EditRecord
{
    protected static string $resource = ProgresTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
