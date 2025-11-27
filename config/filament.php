<?php

return [

    /*
    |-----------------------------------------------------------------
    | Filament Resources
    |-----------------------------------------------------------------
    */
    'resources' => [
        App\Filament\Resources\TransaksiProdukResource::class,
        App\Filament\Resources\TransaksiJasaResource::class,
        App\Filament\Resources\TeknisiResource::class,
        // ... resource lain
    ],

    /*
    |-----------------------------------------------------------------
    | Filament Panels
    |-----------------------------------------------------------------
    */
    'panels' => [
        'admin' => [
            'id'       => 'admin',
            'path'     => 'admin',
            'login'    => 'login', // sesuaikan route login-mu
            'provider' => App\Providers\Filament\AdminPanelProvider::class,
            'system_route_prefix' => 'filament',
        ],
    ],

    /*
    |-----------------------------------------------------------------
    | Filesystem, assets & cache
    |-----------------------------------------------------------------
    */
    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),
    'assets_path'             => null,
    'cache_path'              => base_path('bootstrap/cache/filament'),
    'livewire_loading_delay'  => 'default',
    'system_route_prefix'     => 'filament',
];
