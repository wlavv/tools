<?php

return [
    'layout' => 'layouts.app',
    'route_prefix' => 'settings/environment-manager',
    'middleware' => ['web', 'auth'],
    'pagination' => 75,

    'modules_path' => 'Modules',
    'env_file' => '.env',
    'scan_module_config_files' => true,
    'mask_sensitive_values' => true,

    'sensitive_patterns' => [
        'APP_KEY', 'PASSWORD', 'PASSWD', 'SECRET', 'TOKEN', 'PRIVATE_KEY', 'PUBLIC_KEY',
        'API_KEY', 'ACCESS_KEY', 'AUTHORIZATION', 'COOKIE', 'DATABASE_URL', 'DB_URL',
        'CONNECTION_STRING', 'DSN', 'WEBHOOK_SECRET', 'CLIENT_SECRET', 'STRIPE_SECRET',
        'JWT', 'ENCRYPTION', 'CREDENTIAL',
    ],

    'runtime_env' => [
        'include_server' => false,
        'deny_keys' => ['argv', 'argc'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configs de módulos criados no B.O.
    |--------------------------------------------------------------------------
    |
    | Opcional. Se estas tabelas/colunas não existirem, o módulo ignora-as.
    |
    */
    'bo_module_configs' => [
        'enabled' => true,
        'rows_limit' => 500,
        'metadata_tables' => ['modules', 'bo_modules'],
        'config_tables' => ['module_configs', 'module_settings', 'bo_module_configs', 'bo_module_settings'],
        'module_key_columns' => ['module_key', 'module_slug', 'module', 'slug', 'name'],
        'module_name_columns' => ['module_name', 'name', 'title', 'label'],
        'config_key_columns' => ['key', 'config_key', 'setting_key', 'name'],
        'value_columns' => ['value', 'config_value', 'setting_value'],
        'json_columns' => ['config', 'configs', 'settings', 'configuration', 'options'],
        'type_columns' => ['type', 'value_type'],
        'sensitive_columns' => ['sensitive', 'is_sensitive', 'secret', 'is_secret'],
        'description_columns' => ['description', 'label', 'title'],
        'enabled_columns' => ['enabled', 'is_enabled', 'active', 'is_active', 'status'],
    ],
];
