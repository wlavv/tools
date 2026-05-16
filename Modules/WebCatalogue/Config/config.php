<?php

return [
    'route_prefix' => env('WEBCATALOGUE_ROUTE_PREFIX', 'webcatalogue'),
    'middleware' => ['web', 'auth'],
    'public_middleware' => ['web'],
    'front_middleware' => ['web'],
    'api_middleware' => ['web', 'throttle:60,1'],
    'front_visible_statuses' => ['published', 'active'],
    'public_route_prefix' => env('WEBCATALOGUE_PUBLIC_ROUTE_PREFIX', 'wc'),
    'api_route_prefix' => env('WEBCATALOGUE_API_ROUTE_PREFIX', 'webcatalogue/api'),
    'storage_disk' => env('WEBCATALOGUE_STORAGE_DISK', 'public'),
    'storage_root' => env('WEBCATALOGUE_STORAGE_ROOT', 'webcatalogue'),
    'default_currency' => env('WEBCATALOGUE_DEFAULT_CURRENCY', 'EUR'),

    '3d_generation' => [
        // sync = runs immediately in the request; queue = dispatches to Laravel queue.
        'dispatch' => env('WEBCATALOGUE_3D_GENERATION_DISPATCH', 'sync'),

        // Available now: mock, meshy.
        'mode' => env('WEBCATALOGUE_3D_GENERATION_MODE', 'mock'),

        'providers' => [
            'mock' => [],
            'meshy' => [
                'api_key' => env('WEBCATALOGUE_MESHY_API_KEY'),
                'base_url' => env('WEBCATALOGUE_MESHY_BASE_URL', 'https://api.meshy.ai'),
                'ai_model' => env('WEBCATALOGUE_MESHY_AI_MODEL', 'latest'),
                'should_texture' => env('WEBCATALOGUE_MESHY_SHOULD_TEXTURE', true),
                'enable_pbr' => env('WEBCATALOGUE_MESHY_ENABLE_PBR', true),
                'target_formats' => ['glb', 'usdz'],
                'max_images' => env('WEBCATALOGUE_MESHY_MAX_IMAGES', 4),
                'poll_attempts' => env('WEBCATALOGUE_MESHY_POLL_ATTEMPTS', 60),
                'poll_sleep_seconds' => env('WEBCATALOGUE_MESHY_POLL_SLEEP_SECONDS', 10),
                'http_timeout' => env('WEBCATALOGUE_MESHY_HTTP_TIMEOUT', 120),
                'download_timeout' => env('WEBCATALOGUE_MESHY_DOWNLOAD_TIMEOUT', 300),
            ],
        ],
    ],
    'recognition' => [
        // v2.25 defaults tuned for internal pHash matching. Use auto only for confident matches.
        'auto_match_threshold' => env('WEBCATALOGUE_RECOGNITION_AUTO_THRESHOLD', 70),
        'suggestion_threshold' => env('WEBCATALOGUE_RECOGNITION_SUGGESTION_THRESHOLD', 50),
        'max_candidate_images' => env('WEBCATALOGUE_RECOGNITION_MAX_CANDIDATES', 800),
        'center_crop_ratio' => env('WEBCATALOGUE_RECOGNITION_CENTER_CROP_RATIO', 0.82),
        'store_debug_matches' => env('WEBCATALOGUE_RECOGNITION_STORE_DEBUG_MATCHES', true),
        'debug_top' => env('WEBCATALOGUE_RECOGNITION_DEBUG_TOP', 20),
        'object_crop_enabled' => env('WEBCATALOGUE_RECOGNITION_OBJECT_CROP', true),
        'object_crop_threshold' => env('WEBCATALOGUE_RECOGNITION_OBJECT_CROP_THRESHOLD', 28),
        'fingerprint_rebuild' => [
            'enabled' => env('WEBCATALOGUE_RECOGNITION_REBUILD_ENABLED', true),
            'daily_at' => env('WEBCATALOGUE_RECOGNITION_REBUILD_DAILY_AT', '03:30'),
            'days_per_cycle' => env('WEBCATALOGUE_RECOGNITION_REBUILD_DAYS', 7),
            'queue' => env('WEBCATALOGUE_RECOGNITION_REBUILD_QUEUE', 'webcatalogue_recognition'),
        ],
        'composite_weights' => [
            'phash' => (float) env('WEBCATALOGUE_RECOGNITION_WEIGHT_PHASH', 0.45),
            'edge' => (float) env('WEBCATALOGUE_RECOGNITION_WEIGHT_EDGE', 0.35),
            'color' => (float) env('WEBCATALOGUE_RECOGNITION_WEIGHT_COLOR', 0.20),
        ],
    ],
];
