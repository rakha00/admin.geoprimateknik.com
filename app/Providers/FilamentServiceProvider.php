<?php

namespace App\Providers;

use Filament\PluginServiceProvider;
use App\Filament\Resources\TransaksiJasaResource;
use App\Filament\Resources\TransaksiProdukResource;
use App\Filament\Resources\TeknisiResource;

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
