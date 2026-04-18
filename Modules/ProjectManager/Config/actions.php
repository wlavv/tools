<?php

return [
    'module_home_routes' => [
        'project_manager' => 'project_manager.index',
    ],

    'routes' => [
        'project_manager.index' => [
            'new' => 'project_manager.create',
        ],

        'project_manager.create' => [
            'back' => 'project_manager.index',
            'save' => true,
            'delete' => false,
            'edit' => false,
        ],

        'project_manager.show' => [
            'back' => 'project_manager.index',
            'edit' => 'project_manager.edit',
            'delete' => true,
            'new' => 'project_manager.create',
        ],

        'project_manager.edit' => [
            'back' => 'project_manager.index',
            'new' => 'project_manager.create',
            'save' => true,
            'delete' => true,
        ],
    ],
];
