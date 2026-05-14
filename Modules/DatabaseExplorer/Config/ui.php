<?php

return [
    'module_name' => 'Database Explorer',
    'icon' => 'fa-solid fa-database',
    'empty_state' => [
        'title' => 'No database objects found',
        'text' => 'No tables matched the current filters or allowed schema configuration.',
    ],
    'statuses' => [
        'healthy' => 'Healthy',
        'warning' => 'Warning',
        'degraded' => 'Degraded',
        'critical' => 'Critical',
    ],
];
