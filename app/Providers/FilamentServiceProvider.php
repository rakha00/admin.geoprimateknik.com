<?php

namespace App\Providers;

use App\Filament\Resources\TeknisiResource;
use App\Filament\Resources\TransaksiJasaResource;
use App\Filament\Resources\TransaksiProdukResource;
use Filament\PluginServiceProvider;

class FilamentServiceProvider extends PluginServiceProvider
{
    protected function getResources(): array
    {
        return [
            TransaksiJasaResource::class,
            TransaksiProdukResource::class,
            TeknisiResource::class,
            // daftar resource lain di sini...
        ];
    }
}
