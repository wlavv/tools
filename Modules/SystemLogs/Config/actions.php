<?php

return [
    'module_home_routes' => [
        'system_logs' => 'system_logs.index',
    ],

    'routes' => [
        'system_logs.index' => [
            'module_home_route' => 'system_logs.index',
            'new' => false,
            'refresh' => [
                'label' => 'Refresh',
                'icon' => 'fa-solid fa-rotate',
                'class' => 'lsg-action-btn lsg-action-btn--neutral',
                'route' => 'system_logs.index',
                'type' => 'link',
            ],
            'export' => [
                'label' => 'Export',
                'icon' => 'fa-solid fa-file-csv',
                'class' => 'lsg-action-btn lsg-action-btn--neutral',
                'route' => 'system_logs.export',
                'type' => 'link',
            ],
            'clear' => [
                'label' => 'Clear',
                'icon' => 'fa-solid fa-trash',
                'class' => 'lsg-action-btn lsg-action-btn--danger',
                'route' => 'system_logs.clear',
                'type' => 'link',
                'confirm' => 'system-logs::messages.confirm.clear_message',
            ],
        ],
    ],
];
