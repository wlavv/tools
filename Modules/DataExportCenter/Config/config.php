<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */
    'route_prefix' => 'settings/data-export-center',
    'route_middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Registered exportable models/classes
    |--------------------------------------------------------------------------
    |
    | A class can implement ExportableContract or, when allow_importables is true,
    | it can reuse the DataImportWizard ImportableContract/importFields/importDependencies.
    |
    */
    'exportables' => [
        // \Modules\Catalog\Models\Product::class,
        // \Modules\Orders\Models\Order::class,
    ],

    'registry' => [
        'include_database_profiles' => true,
        'allow_importables_as_exportables' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dependency tree reuse
    |--------------------------------------------------------------------------
    */
    'dependencies' => [
        'reuse_import_wizard_tree' => true,
        'required_dependencies_as_inner_join' => false,
        'default_owner_key' => 'id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Export execution
    |--------------------------------------------------------------------------
    */
    'storage_disk' => env('DATA_EXPORT_CENTER_DISK', 'local'),
    'storage_path' => 'data-export-center/exports',
    'max_rows' => 50000,
    'chunk_size' => 1000,
    'default_format' => 'csv',
    'allowed_formats' => ['csv', 'json', 'html', 'pdf'],

    'csv' => [
        'delimiter' => ';',
        'enclosure' => '"',
        'escape' => '\\',
        'include_bom' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | SELECT-only query profiles
    |--------------------------------------------------------------------------
    */
    'sql' => [
        'allow_cte' => true,
        'forbid_multiple_statements' => true,
        'append_limit_when_missing' => true,
        'forbidden_keywords' => [
            'insert', 'update', 'delete', 'drop', 'alter', 'truncate', 'create',
            'replace', 'merge', 'call', 'exec', 'execute', 'grant', 'revoke',
            'load', 'handler', 'lock', 'unlock', 'attach', 'detach',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic query builder profiles
    |--------------------------------------------------------------------------
    |
    | Keep this whitelist strict. The dynamic builder should not become a generic
    | SQL execution console.
    |
    */
    'dynamic_builder' => [
        'allowed_tables' => [
            // 'products', 'suppliers', 'orders',
        ],
        'allow_raw_selects' => false,
        'max_joins' => 10,
        'max_selects' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Report templates
    |--------------------------------------------------------------------------
    */
    'reports' => [
        'max_rows' => 5000,
        'default_title' => 'Export Report',
        'default_scope_type' => 'global',
        'template_context_keys' => [
            'shop_id', 'shop_key', 'platform', 'module', 'profile_key',
        ],
        'pdf' => [
            'enabled' => true,
            'dompdf_facade' => \Barryvdh\DomPDF\Facade\Pdf::class,
        ],
    ],
];
