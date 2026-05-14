<?php

return [
    'module_home_routes' => [
        'streamdeck_access' => 'streamdeck_access.index',
    ],

    'routes' => [
        'streamdeck_access.index' => [
            'new' => 'streamdeck_access.create',
        ],

        'streamdeck_access.create' => [
            'back'   => 'streamdeck_access.index',
            'save'   => true,
            'delete' => false,
            'edit'   => false,
        ],

        'streamdeck_access.show' => [
            'back'   => 'streamdeck_access.index',
            'edit'   => 'streamdeck_access.edit',
            'delete' => true,
            'new'    => 'streamdeck_access.create',
        ],

        'streamdeck_access.edit' => [
            'back'   => 'streamdeck_access.index',
            'new'    => 'streamdeck_access.create',
            'save'   => true,
            'delete' => true,
        ],
    ],
];
