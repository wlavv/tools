<?php

return [
    'module_home_routes' => [
        'environment_manager' => 'environment_manager.index',
    ],

    'routes' => [
        'environment_manager.index' => [
            'new' => false,
            'save' => false,
            'edit' => false,
            'delete' => false,
        ],

        'environment_manager.env' => [
            'back' => 'environment_manager.index',
            'new' => false,
            'save' => false,
            'edit' => false,
            'delete' => false,
        ],

        'environment_manager.config' => [
            'back' => 'environment_manager.index',
            'new' => false,
            'save' => false,
            'edit' => false,
            'delete' => false,
        ],

        'environment_manager.modules' => [
            'back' => 'environment_manager.index',
            'new' => false,
            'save' => false,
            'edit' => false,
            'delete' => false,
        ],

        'environment_manager.modules.show' => [
            'back' => 'environment_manager.modules',
            'new' => false,
            'save' => false,
            'edit' => false,
            'delete' => false,
        ],

        'environment_manager.effective' => [
            'back' => 'environment_manager.index',
            'new' => false,
            'save' => false,
            'edit' => false,
            'delete' => false,
        ],
    ],
];
