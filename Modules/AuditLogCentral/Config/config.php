<?php

return [
    'name' => 'Audit Log Central',
    'slug' => 'audit-log-central',
    'route_prefix' => 'settings/audit-log-central',
    'route_name' => 'audit_log_central.',
    'layout' => env('AUDIT_LOG_LAYOUT', 'audit-log-central::layouts.module'),
    'enabled' => true,
    'retention_days' => [
        'debug' => 15,
        'info' => 365,
        'notice' => 730,
        'warning' => 1095,
        'error' => 1825,
        'critical' => 1825,
        'security' => 1825,
    ],
    'default_severity' => 'info',
    'severities' => ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'security'],
    'sensitive_keys' => [
        'password', 'password_confirmation', 'token', 'api_token', 'secret', 'client_secret',
        'authorization', 'cookie', 'remember_token', 'access_token', 'refresh_token',
    ],
    'max_payload_length' => 65535,
];
