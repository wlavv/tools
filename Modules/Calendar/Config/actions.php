<?php

return [
    'module_home_routes' => [
        'calendar' => 'calendar.index',
    ],

    'routes' => [
        'calendar.index' => [
            'new' => 'calendar.events.create',
        ],

        'calendar.contexts.index' => [
            'back' => 'calendar.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'calendar.categories.index' => [
            'back' => 'calendar.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'calendar.events.index' => [
            'back' => 'calendar.index',
            'new' => 'calendar.events.create',
            'edit' => false,
            'delete' => false,
        ],

        'calendar.events.create' => [
            'back' => 'calendar.events.index',
            'save' => true,
        ],

        'calendar.events.show' => [
            'back' => 'calendar.events.index',
            'new' => 'calendar.events.create',
            'edit' => false,
            'delete' => false,
        ],

        'calendar.tablet' => [
            'back' => 'calendar.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],
    ],
];
