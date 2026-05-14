<?php

return [
    'enabled' => env('MODULE_DEPENDENCY_MAP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Backoffice integration
    |--------------------------------------------------------------------------
    */
    'layout' => env('MODULE_DEPENDENCY_MAP_LAYOUT', 'module-dependency-map::layouts.module'),
    'route_prefix' => env('MODULE_DEPENDENCY_MAP_ROUTE_PREFIX', 'settings/module-dependency-map'),
    'route_name' => env('MODULE_DEPENDENCY_MAP_ROUTE_NAME', 'module-dependency-map.'),
    'middleware' => ['web', 'auth'],

    /*
    |--------------------------------------------------------------------------
    | Module discovery
    |--------------------------------------------------------------------------
    */
    'modules_path' => base_path('Modules'),
    'namespace_prefix' => 'Modules',

    'ignored_modules' => [
        'ModuleDependencyMap',
    ],

    'ignored_directories' => [
        'vendor',
        'node_modules',
        'storage',
        '.git',
        'dist',
        'build',
        'public',
    ],

    'file_extensions' => [
        'php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Freshness and risk
    |--------------------------------------------------------------------------
    */
    'fresh_days' => 15,

    'critical_modules' => [
        // Example: 'OMS', 'Stock', 'Pricing', 'Permissions', 'VAT', 'Logistics',
    ],

    'risk_weights' => [
        'direct_dependency' => 3,
        'dependent' => 7,
        'circular_dependency' => 25,
        'critical_dependency' => 10,
        'stale_dependency' => 5,
    ],

    'health_thresholds' => [
        'warning' => 30,
        'risky' => 60,
        'critical' => 80,
    ],

    'max_reference_length' => 500,
    'scan_timeout_seconds' => 120,
];
