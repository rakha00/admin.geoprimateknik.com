<?php

namespace App\Filament\Resources\PrincipleSubdealerResource\Pages;

use App\Filament\Resources\PrincipleSubdealerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPrincipleSubdealer extends EditRecord
{
    protected static string $resource = PrincipleSubdealerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
