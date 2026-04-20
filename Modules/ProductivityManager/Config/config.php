<?php

return [
    'refresh_seconds' => 30,
    'today_limit' => 5,
    'alert_limit' => 8,
    'allowed_webhook_sources' => ['streamdeck', 'n8n', 'manual'],
    'layout' => 'layouts.app',
    'menu' => [
        'title' => 'Productivity',
        'icon' => 'fa-solid fa-gauge-high',
        'route' => 'productivity_manager.index',
    ],
];
