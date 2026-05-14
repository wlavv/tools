<?php

return [
    'name' => 'Integration Health',
    'slug' => 'integration-health',
    'route_prefix' => 'settings/integration-health',
    'route_name' => 'integration_health.',
    'layout' => env('INTEGRATION_HEALTH_LAYOUT', 'integration-health::layouts.module'),

    'thresholds' => [
        'heartbeat_warning_minutes' => 10,
        'heartbeat_critical_minutes' => 30,
        'latency_warning_ms' => 1000,
        'latency_critical_ms' => 3000,
        'error_rate_warning' => 5,
        'error_rate_critical' => 15,
        'queue_pending_warning' => 50,
        'queue_pending_critical' => 200,
    ],

    'statuses' => [
        'online',
        'degraded',
        'offline',
        'unknown',
    ],

    'severities' => [
        'info',
        'notice',
        'warning',
        'error',
        'critical',
        'fatal',
    ],

    'default_services' => [
        ['slug' => 'prestashop', 'name' => 'PrestaShop', 'type' => 'database'],
        ['slug' => 'oms', 'name' => 'OMS', 'type' => 'module'],
        ['slug' => 'moloni', 'name' => 'Moloni', 'type' => 'api'],
        ['slug' => 'vies', 'name' => 'VIES VAT', 'type' => 'api'],
        ['slug' => 'graph-mail', 'name' => 'Microsoft Graph Mail', 'type' => 'api'],
        ['slug' => 'queue-worker', 'name' => 'Queue Worker', 'type' => 'queue'],
        ['slug' => 'scheduler', 'name' => 'Laravel Scheduler', 'type' => 'cron'],
    ],
];
