<?php

return [
    'module_home_routes' => [
        'permission_role_manager' => 'permission_role_manager.dashboard',
    ],

    'routes' => [
        'permission_role_manager.dashboard' => [
            'new' => false,
            'users' => [
                'label' => 'Users',
                'route' => 'permission_role_manager.users.index',
                'icon' => 'fa-solid fa-users',
            ],
            'roles' => [
                'label' => 'Perfis',
                'route' => 'permission_role_manager.roles.index',
                'icon' => 'fa-solid fa-user-shield',
            ],
            'route_access' => [
                'label' => 'Route Access',
                'route' => 'permission_role_manager.route_access.index',
                'icon' => 'fa-solid fa-route',
            ],
            'matrix' => [
                'label' => 'Matrix',
                'route' => 'permission_role_manager.matrix.index',
                'icon' => 'fa-solid fa-table-cells',
            ],
            'permissions' => [
                'label' => 'Permissions',
                'route' => 'permission_role_manager.permissions.index',
                'icon' => 'fa-solid fa-key',
            ],
        ],

        'permission_role_manager.roles.index' => [
            'back' => 'permission_role_manager.dashboard',
            'new' => 'permission_role_manager.roles.create',
        ],

        'permission_role_manager.roles.create' => [
            'back' => 'permission_role_manager.roles.index',
            'save' => true,
        ],

        'permission_role_manager.roles.edit' => [
            'back' => 'permission_role_manager.roles.index',
            'save' => true,
            'show' => false,
        ],

        'permission_role_manager.permissions.index' => [
            'back' => 'permission_role_manager.dashboard',
            'new' => 'permission_role_manager.permissions.create',
        ],

        'permission_role_manager.permissions.create' => [
            'back' => 'permission_role_manager.permissions.index',
            'save' => true,
        ],

        'permission_role_manager.permissions.edit' => [
            'back' => 'permission_role_manager.permissions.index',
            'save' => true,
            'show' => false,
        ],

        'permission_role_manager.users.index' => [
            'new' => 'permission_role_manager.users.create',
            'back' => 'permission_role_manager.dashboard',
        ],

        'permission_role_manager.users.create' => [
            'back' => 'permission_role_manager.users.index',
            'save' => true,
        ],

        'permission_role_manager.users.edit' => [
            'back' => 'permission_role_manager.users.index',
            'save' => true,
            'show' => false,
            'delete' => false,
        ],

        'permission_role_manager.matrix.index' => [
            'new' => false,
            'back' => 'permission_role_manager.dashboard',
        ],

        'permission_role_manager.route_access.index' => [
            'new' => false,
            'back' => 'permission_role_manager.dashboard',
        ],

        'permission_role_manager.inspector.index' => [
            'new' => false,
            'back' => 'permission_role_manager.dashboard',
        ],

        'permission_role_manager.audit.index' => [
            'new' => false,
            'back' => 'permission_role_manager.dashboard',
        ],

        'permission_role_manager.settings.index' => [
            'new' => false,
            'back' => 'permission_role_manager.dashboard',
        ],
    ],
];
