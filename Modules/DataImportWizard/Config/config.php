<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'route_prefix' => 'settings/data-import-wizard',
    'route_middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Registered importable models
    |--------------------------------------------------------------------------
    |
    | Cada model deve implementar ImportableContract e, idealmente, usar
    | HasImportContract.
    |
    */
    'importables' => [
        \Modules\CatalogManager\Models\CatalogManufacturer::class,
        \Modules\CatalogManager\Models\CatalogSupplier::class,
        \Modules\CatalogManager\Models\CatalogProduct::class,
        \Modules\CatalogManager\Models\CatalogStore::class,
        \Modules\CatalogManager\Models\CatalogStoreCategory::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | CSV
    |--------------------------------------------------------------------------
    */
    'csv' => [
        'delimiter' => null, // null = auto-detect: comma, semicolon, tab
        'include_example_row_by_default' => false,
        'max_rows' => 10000,
        'allowed_extensions' => ['csv', 'txt'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Import execution
    |--------------------------------------------------------------------------
    */
    'strict_headers' => false,
    'default_mode' => 'upsert',
    'strict_row_transactions' => true,

    /*
    |--------------------------------------------------------------------------
    | Discovery
    |--------------------------------------------------------------------------
    */
    'modules_path' => base_path('Modules'),
];
