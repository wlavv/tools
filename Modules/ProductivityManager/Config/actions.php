<?php

return [
    'module_home_routes' => [
        'productivity_manager' => 'productivity_manager.index',
    ],

    'routes' => [
        'productivity_manager.index' => [
            'show' => false,
            'edit' => false,
            'delete' => false,
            'new' => false,
        ],

        'productivity_manager.dashboard' => [
            'back' => 'productivity_manager.index',
        ],

        'productivity_manager.settings' => [
            'back' => 'productivity_manager.index',
        ],
    ],
];
