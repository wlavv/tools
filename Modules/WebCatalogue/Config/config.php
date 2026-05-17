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
    'temporary_tcg_seed_token' => env('WEBCATALOGUE_TEMP_TCG_SEED_TOKEN'),

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
        'multi_frame_count' => env('WEBCATALOGUE_RECOGNITION_MULTI_FRAME_COUNT', 3),
        'opencv' => [
            'enabled' => env('WEBCATALOGUE_RECOGNITION_OPENCV_ENABLED', false),
            'base_url' => env('WEBCATALOGUE_RECOGNITION_OPENCV_BASE_URL'),
            'token' => env('WEBCATALOGUE_RECOGNITION_OPENCV_TOKEN'),
            'timeout' => env('WEBCATALOGUE_RECOGNITION_OPENCV_TIMEOUT', 20),
            'store_debug_image' => env('WEBCATALOGUE_RECOGNITION_OPENCV_STORE_DEBUG', true),
            'score_boost' => env('WEBCATALOGUE_RECOGNITION_OPENCV_SCORE_BOOST', 3),
        ],
        'auto_match_min_margin' => env('WEBCATALOGUE_RECOGNITION_AUTO_MIN_MARGIN', 5),
        'structured_regions_enabled' => env('WEBCATALOGUE_RECOGNITION_STRUCTURED_REGIONS_ENABLED', false),
        'store_structured_regions' => env('WEBCATALOGUE_RECOGNITION_STORE_STRUCTURED_REGIONS', false),
        'embedding_precision' => env('WEBCATALOGUE_RECOGNITION_EMBEDDING_PRECISION', 4),
        'short_hash_top_candidates' => env('WEBCATALOGUE_RECOGNITION_SHORT_HASH_TOP', 20),
        'visual_markers' => [
            'enabled' => env('WEBCATALOGUE_RECOGNITION_MARKERS_ENABLED', true),
            'algorithm' => env('WEBCATALOGUE_RECOGNITION_MARKERS_ALGORITHM', 'orb_v1'),
            'max_markers' => env('WEBCATALOGUE_RECOGNITION_MARKERS_MAX', 250),
            'min_markers' => env('WEBCATALOGUE_RECOGNITION_MARKERS_MIN', 40),
        ],
        'store_full_fingerprint_profile' => env('WEBCATALOGUE_RECOGNITION_STORE_FULL_PROFILE', false),
        'region_global_weight' => env('WEBCATALOGUE_RECOGNITION_REGION_GLOBAL_WEIGHT', 0.45),
        'region_structured_weight' => env('WEBCATALOGUE_RECOGNITION_REGION_STRUCTURED_WEIGHT', 0.55),
        'region_weights' => [
            'art' => (float) env('WEBCATALOGUE_RECOGNITION_REGION_WEIGHT_ART', 0.45),
            'name' => (float) env('WEBCATALOGUE_RECOGNITION_REGION_WEIGHT_NAME', 0.30),
            'text' => (float) env('WEBCATALOGUE_RECOGNITION_REGION_WEIGHT_TEXT', 0.20),
            'footer' => (float) env('WEBCATALOGUE_RECOGNITION_REGION_WEIGHT_FOOTER', 0.05),
        ],
        'fingerprint_rebuild' => [
            'enabled' => env('WEBCATALOGUE_RECOGNITION_REBUILD_ENABLED', true),
            'daily_at' => env('WEBCATALOGUE_RECOGNITION_REBUILD_DAILY_AT', '03:30'),
            'days_per_cycle' => env('WEBCATALOGUE_RECOGNITION_REBUILD_DAYS', 7),
            'queue' => env('WEBCATALOGUE_RECOGNITION_REBUILD_QUEUE', 'webcatalogue_recognition'),
        ],
        'composite_weights' => [
            'embedding' => (float) env('WEBCATALOGUE_RECOGNITION_WEIGHT_EMBEDDING', 0.35),
            'phash' => (float) env('WEBCATALOGUE_RECOGNITION_WEIGHT_PHASH', 0.30),
            'edge' => (float) env('WEBCATALOGUE_RECOGNITION_WEIGHT_EDGE', 0.20),
            'color' => (float) env('WEBCATALOGUE_RECOGNITION_WEIGHT_COLOR', 0.15),
        ],
        'max_scored_candidates' => env('WEBCATALOGUE_RECOGNITION_MAX_SCORED_CANDIDATES', 160),
    ],
];
