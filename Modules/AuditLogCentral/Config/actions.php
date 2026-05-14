<?php

return [
    'module_home_routes' => [
        'audit_log_central' => 'audit_log_central.dashboard',
    ],

    'routes' => [
        'audit_log_central.dashboard' => [
            'logs' => [
                'label' => 'Logs',
                'route' => 'audit_log_central.index',
                'icon' => 'fa-solid fa-clipboard-list',
            ],
        ],

        'audit_log_central.index' => [
            'new' => false,
            'back' => 'audit_log_central.dashboard',
            'refresh' => [
                'label' => 'Refresh',
                'route' => 'audit_log_central.index',
                'icon' => 'fa-solid fa-rotate-right',
            ],
        ],

        'audit_log_central.show' => [
            'back' => 'audit_log_central.index',
            'edit' => false,
            'delete' => false,
        ],

        'audit_log_central.entity.timeline' => [
            'new' => false,
            'back' => 'audit_log_central.index',
        ],
    ],
];
