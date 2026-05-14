<?php

return [
    'module_home_routes' => [
        'config_inspector' => 'config_inspector.index',
    ],

    'routes' => [
        'config_inspector.index' => [
            'new' => false,
            'refresh' => [
                'label' => 'Refresh',
                'route' => 'config_inspector.index',
                'icon' => 'fa-solid fa-rotate-right',
            ],
        ],
    ],
];
