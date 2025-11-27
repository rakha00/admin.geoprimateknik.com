<?php

namespace App\Filament\Resources\PengeluaranTransaksiProdukResource\Pages;

use App\Filament\Resources\PengeluaranTransaksiProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengeluaranTransaksiProduks extends ListRecords
{
    protected static string $resource = PengeluaranTransaksiProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
