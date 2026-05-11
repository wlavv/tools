<?php

return [
    'module_home_routes' => [
        'mtg' => 'mtg.index',
    ],

    'routes' => [
        'mtg.index' => [
            'new' => false,
        ],

        'mtg.showSet' => [
            'back' => 'mtg.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'mtg.findCard' => [
            'back' => 'mtg.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],
    ],
];
