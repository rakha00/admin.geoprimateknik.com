<?php

namespace App\Filament\Resources\TransaksiJasaResource\Pages;

use App\Filament\Resources\TransaksiJasaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiJasas extends ListRecords
{
    protected static string $resource = TransaksiJasaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
