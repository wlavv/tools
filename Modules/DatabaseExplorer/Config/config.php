<?php

return [
    'enabled' => env('DB_EXPLORER_ENABLED', true),

    'layout' => 'layouts.app',
    'pagination' => 50,

    'connection' => env('DB_EXPLORER_CONNECTION', env('DB_CONNECTION', 'mysql')),
    'driver' => env('DB_EXPLORER_DRIVER', env('DB_CONNECTION', 'mysql')),

    'route_prefix' => env('DB_EXPLORER_ROUTE_PREFIX', 'settings/database-explorer'),
    'middleware' => array_values(array_filter(array_map('trim', explode(',', env('DB_EXPLORER_MIDDLEWARE', 'web,auth'))))),

    'allowed_schemas' => env('DB_EXPLORER_ALLOWED_SCHEMAS', 'public') === '*'
        ? []
        : array_values(array_filter(array_map('trim', explode(',', env('DB_EXPLORER_ALLOWED_SCHEMAS', 'public'))))),

    'excluded_schemas' => array_values(array_filter(array_map('trim', explode(',', env('DB_EXPLORER_EXCLUDED_SCHEMAS', 'information_schema,pg_catalog,pg_toast'))))),
    'mysql_excluded_schemas' => array_values(array_filter(array_map('trim', explode(',', env('DB_EXPLORER_MYSQL_EXCLUDED_SCHEMAS', 'information_schema,mysql,performance_schema,sys'))))),

    'snapshots' => [
        'enabled' => env('DB_EXPLORER_SNAPSHOTS_ENABLED', true),
        'connection' => env('DB_EXPLORER_SNAPSHOT_CONNECTION', env('DB_EXPLORER_CONNECTION', env('DB_CONNECTION', 'pgsql'))),
        'retention_days' => (int) env('DB_EXPLORER_SNAPSHOT_RETENTION_DAYS', 90),
    ],

    'health' => [
        'stale_statistics_days' => (int) env('DB_EXPLORER_STALE_STATISTICS_DAYS', 7),
        'max_indexes_warning' => (int) env('DB_EXPLORER_MAX_INDEXES_WARNING', 15),
        'dead_row_ratio_warning' => (float) env('DB_EXPLORER_DEAD_ROW_RATIO_WARNING', 0.20),
        'dead_row_ratio_critical' => (float) env('DB_EXPLORER_DEAD_ROW_RATIO_CRITICAL', 0.40),
        'index_to_data_ratio_warning' => (float) env('DB_EXPLORER_INDEX_TO_DATA_RATIO_WARNING', 1.00),
        'unused_index_min_size_bytes' => (int) env('DB_EXPLORER_UNUSED_INDEX_MIN_SIZE_BYTES', 10485760),
        'large_table_bytes' => (int) env('DB_EXPLORER_LARGE_TABLE_BYTES', 10737418240),
    ],
];
