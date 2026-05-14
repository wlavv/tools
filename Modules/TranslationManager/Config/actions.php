<?php

return [
    'module_home_routes' => [
        'translation_manager' => 'translation_manager.index',
    ],

    'routes' => [
        'translation_manager.index' => [
            'new' => false,
            'refresh' => [
                'label' => 'Refresh',
                'route' => 'translation_manager.index',
                'icon' => 'fa-solid fa-rotate',
            ],
        ],
    ],
];
