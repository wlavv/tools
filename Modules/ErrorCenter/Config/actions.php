<?php

return [
    'module_home_routes' => [
        'error-center' => 'error-center.index',
    ],

    'routes' => [
        'error-center.index' => [
            'new' => false,
            'refresh' => [
                'label' => 'Refresh',
                'route' => 'error-center.index',
                'icon' => 'fa-solid fa-rotate',
            ],
        ],

        'error-center.show' => [
            'back' => 'error-center.index',
            'edit' => false,
            'delete' => false,
        ],
    ],
];
