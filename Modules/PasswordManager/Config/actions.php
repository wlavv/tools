<?php

return [
    'module_home_routes' => [
        'password_manager' => 'password_manager.index',
    ],

    'routes' => [
        'password_manager.index' => [
            'new' => 'password_manager.create',
        ],

        'password_manager.create' => [
            'back'   => 'password_manager.index',
            'save'   => true,
            'delete' => false,
            'edit'   => false,
        ],

        'password_manager.show' => [
            'back'   => 'password_manager.index',
            'edit'   => 'password_manager.edit',
            'delete' => true,
            'new'    => 'password_manager.create',
        ],

        'password_manager.edit' => [
            'back'   => 'password_manager.index',
            'new'    => 'password_manager.create',
            'save'   => true,
            'delete' => true,
        ],
    ],
];