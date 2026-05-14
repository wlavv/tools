<?php

return [
    'module_home_routes' => [
        'database_explorer' => 'database_explorer.index',
    ],

    'routes' => [
        'database_explorer.index' => [
            'refresh' => 'database_explorer.index',
            'health' => 'database_explorer.health',
            'snapshots' => 'database_explorer.snapshots',
        ],

        'database_explorer.show' => [
            'back' => 'database_explorer.index',
            'health' => 'database_explorer.health',
        ],

        'database_explorer.health' => [
            'back' => 'database_explorer.index',
            'snapshots' => 'database_explorer.snapshots',
        ],

        'database_explorer.snapshots' => [
            'back' => 'database_explorer.index',
        ],
    ],
];
