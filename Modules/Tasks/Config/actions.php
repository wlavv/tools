<?php

return [
    'module_home_routes' => [
        'tasks' => 'tasks.index',
    ],

    'routes' => [
        'tasks.index' => [
            'new' => false,
        ],

        'tasks.dashboard' => [
            'back' => 'tasks.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'tasks.calendar' => [
            'back' => 'tasks.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'tasks.members.index' => [
            'back' => 'tasks.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'tasks.events.index' => [
            'back' => 'tasks.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'tasks.manage.index' => [
            'back' => 'tasks.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'tasks.rewards.index' => [
            'back' => 'tasks.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'tasks.tablet' => [
            'back' => 'tasks.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],
    ],
];
