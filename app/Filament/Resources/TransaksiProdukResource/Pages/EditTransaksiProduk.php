<?php

namespace App\Filament\Resources\TransaksiProdukResource\Pages;

use App\Filament\Resources\TransaksiProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransaksiProduk extends EditRecord
{
    protected static string $resource = TransaksiProdukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
