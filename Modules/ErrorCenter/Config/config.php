<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Error Center
    |--------------------------------------------------------------------------
    */
    'enabled' => env('ERROR_CENTER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    |
    | If you use Spatie Permission, add middleware such as:
    |   'permission:error_center.view'
    |   'permission:error_center.manage'
    |
    | If you use Laravel Gates, add middleware such as:
    |   'can:error_center.view'
    |   'can:error_center.manage'
    */
    'route_prefix' => env('ERROR_CENTER_ROUTE_PREFIX', 'settings/error-center'),
    'route_name_prefix' => env('ERROR_CENTER_ROUTE_NAME_PREFIX', 'error-center.'),

    'view_middleware' => array_filter(array_map('trim', explode(',', env('ERROR_CENTER_VIEW_MIDDLEWARE', 'web,auth')))),
    'manage_middleware' => array_filter(array_map('trim', explode(',', env('ERROR_CENTER_MANAGE_MIDDLEWARE', 'web,auth')))),

    /*
    |--------------------------------------------------------------------------
    | Automatic Exception Capture
    |--------------------------------------------------------------------------
    |
    | By default, the module pushes a middleware into the web and api groups.
    | The middleware captures unhandled exceptions and rethrows them to Laravel.
    */
    'capture' => [
        'enabled' => env('ERROR_CENTER_CAPTURE_ENABLED', true),
        'auto_register_middleware' => env('ERROR_CENTER_AUTO_REGISTER_MIDDLEWARE', true),
        'middleware_groups' => array_filter(array_map('trim', explode(',', env('ERROR_CENTER_CAPTURE_MIDDLEWARE_GROUPS', 'web,api')))),
        'excluded_paths' => array_filter(array_map('trim', explode(',', env('ERROR_CENTER_EXCLUDED_PATHS', '')))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Sanitization
    |--------------------------------------------------------------------------
    */
    'sanitizer' => [
        'redacted_value' => '[REDACTED]',
        'max_depth' => 8,
        'max_string_length' => 10000,
        'sensitive_keys' => [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'token',
            'access_token',
            'refresh_token',
            'authorization',
            'api_key',
            'apikey',
            'secret',
            'client_secret',
            'cookie',
            'session',
            'csrf',
            'xsrf',
            'credit_card',
            'card_number',
            'cvv',
            'iban',
            'private_key',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Severity Rules
    |--------------------------------------------------------------------------
    */
    'severity' => [
        'critical_status_codes' => [503],
        'critical_modules' => ['payments', 'billing', 'authentication', 'auth'],
        'critical_exception_classes' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'title_length' => 255,
        'message_length' => 65535,
        'stack_trace_length' => 120000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications Integration
    |--------------------------------------------------------------------------
    |
    | The module emits Modules\ErrorCenter\Events\ErrorCenterNotificationRequested.
    | Your Notifications module should listen to that event and create/send the
    | actual notification. Optionally, set a service class/container key below.
    */
    'notifications' => [
        'enabled' => env('ERROR_CENTER_NOTIFICATIONS_ENABLED', true),
        'environments' => array_filter(array_map('trim', explode(',', env('ERROR_CENTER_NOTIFICATION_ENVIRONMENTS', 'production')))),
        'cooldown_minutes' => (int) env('ERROR_CENTER_NOTIFICATION_COOLDOWN_MINUTES', 30),

        'triggers' => [
            'error_center.critical_created' => true,
            'error_center.resolved_reopened' => true,
            'error_center.error_created' => false,
            'error_center.threshold_reached' => false,
        ],

        'target_role' => env('ERROR_CENTER_NOTIFICATION_TARGET_ROLE', 'technical_admin'),
        'target_permission' => env('ERROR_CENTER_NOTIFICATION_TARGET_PERMISSION', 'error_center.manage'),

        // Example: App\Modules\Notifications\Services\NotificationService::class
        // The service may expose create(array $payload) or send(array $payload).
        'service' => env('ERROR_CENTER_NOTIFICATIONS_SERVICE', null),
    ],
];
