<?php

return [
    'module_home_routes' => [
        'module-dependency-map' => 'module-dependency-map.index',
    ],

    'routes' => [
        'module-dependency-map.index' => [
            'new' => false,
            'run_all' => [
                'label' => 'Run all',
                'route' => 'module-dependency-map.run-all',
                'icon' => 'fa-solid fa-rotate',
                'type' => 'form',
                'method' => 'POST',
                'class' => 'primary',
            ],
        ],

        'module-dependency-map.show' => [
            'back' => 'module-dependency-map.index',
            'run_scan' => [
                'label' => 'Run scan',
                'route' => 'module-dependency-map.run',
                'icon' => 'fa-solid fa-rotate',
                'type' => 'form',
                'method' => 'POST',
                'class' => 'primary',
            ],
            'edit' => false,
            'delete' => false,
        ],
    ],
];
