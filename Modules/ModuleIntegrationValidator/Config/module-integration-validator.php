<?php

return [
    'route_prefix' => 'module-integration-validator',
    'route_name_prefix' => 'module-integration-validator.',
    'view_namespace' => 'module-integration-validator',

    'default_module_base_path' => base_path('Modules'),

    'required_manifest_keys' => [
        'name',
        'slug',
        'version',
        'provider',
        'permissions',
    ],

    'expected_provider_methods' => [
        'register',
        'boot',
    ],

    'expected_provider_calls' => [
        'loadRoutesFrom',
        'loadViewsFrom',
        'loadTranslationsFrom',
    ],

    'optional_provider_calls' => [
        'loadMigrationsFrom',
        'publishes',
    ],

    'permission_prefix' => 'permission_',

    'route_name_recommendation' => 'Use a stable module prefix for named routes, e.g. module-slug.index, module-slug.create, module-slug.store.',

    'asset_patterns' => [
        '@push',
        '@section',
        'mix(',
        'vite(',
        'asset(',
        'module_asset',
    ],

    'integration_files' => [
        'module.json',
        'Providers',
        'routes/web.php',
        'Resources/views',
        'lang/pt',
        'lang/en',
    ],

    'core_write_forbidden_patterns' => [
        'app/Http/Controllers',
        'app/Models',
        'routes/web.php',
        'routes/api.php',
        'resources/views',
        'config/app.php',
    ],
];
