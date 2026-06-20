<?php

return [
    'name' => 'LSG Site Manager',
    'route_prefix' => env('LSG_SITE_MANAGER_ROUTE_PREFIX', 'lsg/site-manager'),
    'route_name' => 'lsg.site_manager.',
    'middleware' => ['web', 'auth'],
    'layout' => 'site-manager::layouts.module',
    'page_titles' => [
        'lsg.site_manager.dashboard' => 'Site Manager',
        'lsg.site_manager.sites.index' => 'Sites LSG',
        'lsg.site_manager.sites.create' => 'Novo site',
        'lsg.site_manager.sites.show' => 'Detalhe do site',
        'lsg.site_manager.sites.edit' => 'Editar site',
    ],
    'pagespeed' => [
        'api_key' => env('GOOGLE_PAGESPEED_API_KEY'),
        'timeout' => (int) env('LSG_PAGESPEED_TIMEOUT', 25),
        'between_requests_ms' => (int) env('LSG_PAGESPEED_BETWEEN_REQUESTS_MS', 1500),
        'daily_strategies' => ['mobile', 'desktop'],
    ],
    'site_types' => [
        'store' => 'Loja',
        'service' => 'Servico',
        'presentation' => 'Capa / apresentacao',
    ],
];
