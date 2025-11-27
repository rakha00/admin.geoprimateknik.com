<?php

namespace App\Filament\Resources\TransaksiProdukResource\Pages;

use App\Filament\Resources\TransaksiProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksiProduks extends ListRecords
{
    protected static string $resource = TransaksiProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
