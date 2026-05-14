<?php

use Modules\StreamDeckAccess\Tasks\CheckExternalLinksTask;
use Modules\StreamDeckAccess\Tasks\PagespeedInsightsTask;
use Modules\StreamDeckAccess\Tasks\PingTask;
use Modules\StreamDeckAccess\Tasks\SalesForecastTask;
use Modules\StreamDeckAccess\Tasks\SalesYesterdayReportTask;

return [
    'layout' => 'layouts.app',
    'pagination' => 15,

    'route_prefix' => 'settings/streamdeck-access',
    'middleware' => ['web', 'auth'],

    'public_route_prefix' => env('STREAMDECK_PUBLIC_ROUTE_PREFIX', 'api/streamdeck'),
    'public_middleware' => ['api', 'throttle:streamdeck-access'],
    'token_parameter' => env('STREAMDECK_TOKEN_PARAMETER', 'token'),
    'rate_limit_per_minute' => (int) env('STREAMDECK_RATE_LIMIT_PER_MINUTE', 30),

    'default_queue' => env('STREAMDECK_QUEUE', 'default'),

    'types' => [
        'redirect' => 'Abrir página / URL',
        'task' => 'Executar tarefa em background',
    ],

    'tasks' => [
        'ping' => PingTask::class,
        'pagespeed_google' => PagespeedInsightsTask::class,
        'check_external_links' => CheckExternalLinksTask::class,
        'sales_yesterday_report' => SalesYesterdayReportTask::class,
        'sales_forecast' => SalesForecastTask::class,
    ],

    'task_labels' => [
        'ping' => 'Teste da queue / ping',
        'pagespeed_google' => 'Google PageSpeed Insights',
        'check_external_links' => 'Verificar ligações externas',
        'sales_yesterday_report' => 'Relatório de vendas do dia anterior',
        'sales_forecast' => 'Previsão de vendas por loja',
    ],

    'allowed_ips' => env('STREAMDECK_ALLOWED_IPS')
        ? array_values(array_filter(array_map('trim', explode(',', env('STREAMDECK_ALLOWED_IPS')))))
        : [],

    'google_pagespeed_api_key' => env('GOOGLE_PAGESPEED_API_KEY'),

    'link_checker_allowed_hosts' => env('STREAMDECK_LINK_CHECKER_ALLOWED_HOSTS')
        ? array_values(array_filter(array_map('trim', explode(',', env('STREAMDECK_LINK_CHECKER_ALLOWED_HOSTS')))))
        : [],
    'link_checker_timeout' => (int) env('STREAMDECK_LINK_CHECKER_TIMEOUT', 10),
    'link_checker_max_urls' => (int) env('STREAMDECK_LINK_CHECKER_MAX_URLS', 25),

    'commands' => [
        'sales_yesterday_report' => env('STREAMDECK_SALES_YESTERDAY_COMMAND', 'reports:sales-yesterday'),
        'sales_forecast' => env('STREAMDECK_SALES_FORECAST_COMMAND', 'reports:sales-forecast'),
    ],
];
