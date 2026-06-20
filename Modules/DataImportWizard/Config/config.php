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
