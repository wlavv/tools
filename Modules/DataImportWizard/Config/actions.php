<?php

return [
    'module_home_routes' => [
        'data_import_wizard' => 'data_import_wizard.dashboard',
    ],

    'routes' => [
        'data_import_wizard.dashboard' => [
            'profiles' => [
                'label' => 'Profiles',
                'route' => 'data_import_wizard.profiles.index',
                'icon' => 'fa-solid fa-list-check',
            ],
            'batches' => [
                'label' => 'Batches',
                'route' => 'data_import_wizard.batches.index',
                'icon' => 'fa-solid fa-layer-group',
            ],
        ],

        'data_import_wizard.profiles.index' => [
            'new' => false,
            'back' => 'data_import_wizard.dashboard',
            'batches' => [
                'label' => 'Batches',
                'route' => 'data_import_wizard.batches.index',
                'icon' => 'fa-solid fa-layer-group',
            ],
        ],

        'data_import_wizard.profiles.show' => [
            'back' => 'data_import_wizard.profiles.index',
            'edit' => false,
            'delete' => false,
        ],

        'data_import_wizard.profiles.upload' => [
            'back' => 'data_import_wizard.profiles.show',
            'save' => true,
        ],

        'data_import_wizard.batches.index' => [
            'new' => false,
            'back' => 'data_import_wizard.dashboard',
            'profiles' => [
                'label' => 'Profiles',
                'route' => 'data_import_wizard.profiles.index',
                'icon' => 'fa-solid fa-list-check',
            ],
        ],

        'data_import_wizard.batches.preview' => [
            'back' => 'data_import_wizard.batches.index',
        ],

        'data_import_wizard.batches.show' => [
            'back' => 'data_import_wizard.batches.index',
            'edit' => false,
            'delete' => false,
        ],
    ],
];
