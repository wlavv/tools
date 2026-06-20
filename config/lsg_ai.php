<?php

return [
    'gateway_url' => env('LSG_AI_GATEWAY_URL', 'https://api-ai.lsg-labs.com'),
    'token' => env('LSG_AI_GATEWAY_TOKEN'),
    'admin_token' => env('LSG_AI_ADMIN_TOKEN'),
    'timeout' => (int) env('LSG_AI_GATEWAY_TIMEOUT', 180),
    'backup_timeout' => (int) env('LSG_AI_BACKUP_TIMEOUT', 300),
    'default_model' => env('LSG_AI_DEFAULT_MODEL', 'qwen2.5:7b'),
];
