<?php

return [
    'module_name' => 'Package Tracker',

    'route_prefix' => 'package-tracker',
    'route_name' => 'package_tracker.',
    'middleware' => ['web', 'auth'],
    'layout' => env('PACKAGE_TRACKER_LAYOUT', 'layouts.app'),

    'queue' => env('PACKAGE_TRACKER_QUEUE', 'default'),

    'public' => [
        'enabled' => env('PACKAGE_TRACKER_PUBLIC_ENABLED', true),
        'route_prefix' => env('PACKAGE_TRACKER_PUBLIC_ROUTE_PREFIX', 'track'),
        'middleware' => ['web'],
        'theme' => [
            'brand_name' => env('PACKAGE_TRACKER_PUBLIC_BRAND_NAME', 'Package Tracker'),
            'logo_url' => env('PACKAGE_TRACKER_PUBLIC_LOGO_URL'),
            'primary_color' => env('PACKAGE_TRACKER_PUBLIC_PRIMARY_COLOR', '#0f766e'),
            'accent_color' => env('PACKAGE_TRACKER_PUBLIC_ACCENT_COLOR', '#2563eb'),
            'background_color' => env('PACKAGE_TRACKER_PUBLIC_BACKGROUND_COLOR', '#f8fafc'),
        ],
    ],

    'integrator_paths' => [
        // Add extra absolute paths here if you want to drop carrier integrators outside this module.
    ],

    'access' => [
        // Useful for internal BO usage. For SaaS tenants set this to false and enable carriers per client.
        'allow_without_client_key' => env('PACKAGE_TRACKER_ALLOW_WITHOUT_CLIENT_KEY', true),
    ],

    'discovery' => [
        // Phase 2 commercial feature: when a contracted carrier does not find a tracking number,
        // probe non-contracted active carriers and create a suggestion to upsell/enable that carrier.
        'probe_uncontracted_carriers' => env('PACKAGE_TRACKER_PROBE_UNCONTRACTED_CARRIERS', false),
    ],

    'http' => [
        'timeout' => (int) env('PACKAGE_TRACKER_HTTP_TIMEOUT', 20),
        'retries' => (int) env('PACKAGE_TRACKER_HTTP_RETRIES', 1),
        'retry_sleep_ms' => (int) env('PACKAGE_TRACKER_HTTP_RETRY_SLEEP_MS', 250),
    ],

    'polling' => [
        'enabled' => env('PACKAGE_TRACKER_POLLING_ENABLED', true),
        'default_interval_minutes' => (int) env('PACKAGE_TRACKER_POLLING_INTERVAL', 60),
        'stale_after_hours' => (int) env('PACKAGE_TRACKER_STALE_AFTER_HOURS', 24),
        'max_attempts' => (int) env('PACKAGE_TRACKER_MAX_ATTEMPTS', 3),
    ],

    'sla' => [
        'default_delivery_days' => (int) env('PACKAGE_TRACKER_DEFAULT_SLA_DAYS', 5),
        'warning_hours_before_breach' => (int) env('PACKAGE_TRACKER_SLA_WARNING_HOURS', 12),
    ],

    'webhooks' => [
        'enabled' => env('PACKAGE_TRACKER_WEBHOOKS_ENABLED', true),
        'timeout' => (int) env('PACKAGE_TRACKER_WEBHOOK_TIMEOUT', 8),
    ],

    'carriers' => [
        'manual' => [
            'label' => 'Manual / Generic',
            'driver' => Modules\PackageTracker\Services\Carriers\ManualCarrierClient::class,
        ],
        'mock' => [
            'label' => 'Mock Carrier',
            'driver' => Modules\PackageTracker\Services\Carriers\MockCarrierClient::class,
        ],
        'dpd' => [
            'label' => 'DPD',
            'driver' => Modules\PackageTracker\Services\Carriers\Drivers\DpdTrackingClient::class,
            'base_url' => env('PACKAGE_TRACKER_DPD_BASE_URL'),
            'api_key' => env('PACKAGE_TRACKER_DPD_API_KEY'),
            'settings' => [
                'tracking_path' => env('PACKAGE_TRACKER_DPD_TRACKING_PATH', 'tracking'),
                'tracking_param' => env('PACKAGE_TRACKER_DPD_TRACKING_PARAM', 'trackingNumber'),
                'method' => env('PACKAGE_TRACKER_DPD_METHOD', 'GET'),
            ],
        ],
        'dhl' => [
            'label' => 'DHL Unified Tracking',
            'driver' => Modules\PackageTracker\Services\Carriers\Drivers\DhlUnifiedTrackingClient::class,
            'base_url' => env('PACKAGE_TRACKER_DHL_BASE_URL', 'https://api-eu.dhl.com'),
            'api_key' => env('PACKAGE_TRACKER_DHL_API_KEY'),
            'settings' => [
                'service' => env('PACKAGE_TRACKER_DHL_SERVICE'),
                'requester_country_code' => env('PACKAGE_TRACKER_DHL_REQUESTER_COUNTRY', 'PT'),
            ],
        ],
        'ctt' => [
            'label' => 'CTT',
            'driver' => Modules\PackageTracker\Services\Carriers\Drivers\CttTrackingClient::class,
            'base_url' => env('PACKAGE_TRACKER_CTT_BASE_URL'),
            'api_key' => env('PACKAGE_TRACKER_CTT_API_KEY'),
            'settings' => [
                'tracking_path' => env('PACKAGE_TRACKER_CTT_TRACKING_PATH', 'tracking'),
                'method' => env('PACKAGE_TRACKER_CTT_METHOD', 'GET'),
            ],
        ],
        'ups' => [
            'label' => 'UPS Tracking',
            'driver' => Modules\PackageTracker\Services\Carriers\Drivers\UpsTrackingClient::class,
            'base_url' => env('PACKAGE_TRACKER_UPS_BASE_URL', 'https://onlinetools.ups.com'),
            'api_key' => env('PACKAGE_TRACKER_UPS_CLIENT_ID'),
            'api_secret' => env('PACKAGE_TRACKER_UPS_CLIENT_SECRET'),
            'settings' => [
                'locale' => env('PACKAGE_TRACKER_UPS_LOCALE', 'en_US'),
                'transaction_src' => env('PACKAGE_TRACKER_UPS_TRANSACTION_SRC', 'LSGPackageTracker'),
                'access_token' => env('PACKAGE_TRACKER_UPS_ACCESS_TOKEN'),
            ],
        ],
        'nacex' => [
            'label' => 'NACEX',
            'driver' => Modules\PackageTracker\Services\Carriers\Drivers\NacexTrackingClient::class,
            'base_url' => env('PACKAGE_TRACKER_NACEX_BASE_URL', 'https://pda.nacex.com/nacex_ws'),
            'api_key' => env('PACKAGE_TRACKER_NACEX_USER'),
            'api_secret' => env('PACKAGE_TRACKER_NACEX_PASSWORD'),
            'settings' => [
                'tracking_path' => env('PACKAGE_TRACKER_NACEX_TRACKING_PATH', 'ws'),
                'method_name' => env('PACKAGE_TRACKER_NACEX_METHOD_NAME', 'getEstadoEnvio'),
            ],
        ],
        'inpost' => [
            'label' => 'InPost',
            'driver' => Modules\PackageTracker\Services\Carriers\Drivers\InpostTrackingClient::class,
            'base_url' => env('PACKAGE_TRACKER_INPOST_BASE_URL', 'https://api-shipx-pl.easypack24.net'),
            'api_key' => env('PACKAGE_TRACKER_INPOST_TOKEN'),
        ],
        'mondial_relay' => [
            'label' => 'Mondial Relay',
            'driver' => Modules\PackageTracker\Services\Carriers\Drivers\MondialRelayTrackingClient::class,
            'base_url' => env('PACKAGE_TRACKER_MONDIAL_RELAY_BASE_URL', 'https://api.mondialrelay.com'),
            'api_key' => env('PACKAGE_TRACKER_MONDIAL_RELAY_ENSEIGNE'),
            'api_secret' => env('PACKAGE_TRACKER_MONDIAL_RELAY_PRIVATE_KEY'),
            'settings' => [
                'language' => env('PACKAGE_TRACKER_MONDIAL_RELAY_LANGUAGE', 'FR'),
            ],
        ],
    ],

    'normalized_statuses' => [
        'pending' => 'Pending',
        'label_created' => 'Label Created',
        'collected' => 'Collected',
        'in_transit' => 'In Transit',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'delivery_failed' => 'Delivery Failed',
        'exception' => 'Exception',
        'returned' => 'Returned',
        'cancelled' => 'Cancelled',
        'unknown' => 'Unknown',
    ],
];
