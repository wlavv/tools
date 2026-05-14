<?php

return [
    'module_home_routes' => [
        'data_export_center' => 'data_export_center.dashboard',
    ],

    'routes' => [
        'data_export_center.dashboard' => [
            'profiles' => [
                'label' => 'Profiles',
                'route' => 'data_export_center.profiles.index',
                'icon' => 'fa-solid fa-list-check',
            ],
            'templates' => [
                'label' => 'Templates',
                'route' => 'data_export_center.templates.index',
                'icon' => 'fa-solid fa-file-lines',
            ],
        ],

        'data_export_center.profiles.index' => [
            'new' => false,
            'back' => 'data_export_center.dashboard',
            'templates' => [
                'label' => 'Templates',
                'route' => 'data_export_center.templates.index',
                'icon' => 'fa-solid fa-file-lines',
            ],
        ],

        'data_export_center.profiles.show' => [
            'back' => 'data_export_center.profiles.index',
            'edit' => false,
            'delete' => false,
        ],

        'data_export_center.batches.show' => [
            'back' => 'data_export_center.profiles.index',
            'edit' => false,
            'delete' => false,
        ],

        'data_export_center.templates.index' => [
            'new' => false,
            'back' => 'data_export_center.dashboard',
            'profiles' => [
                'label' => 'Profiles',
                'route' => 'data_export_center.profiles.index',
                'icon' => 'fa-solid fa-list-check',
            ],
        ],
    ],
];
