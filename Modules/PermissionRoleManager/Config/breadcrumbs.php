<?php

return [
    'permission_role_manager.dashboard' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.dashboard',
        'parent' => 'settings.index',
    ],

    'permission_role_manager.roles.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.roles.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
    'permission_role_manager.roles.create' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.roles.create',
        'parent' => 'permission_role_manager.roles.index',
    ],
    'permission_role_manager.roles.edit' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.roles.edit',
        'parent' => 'permission_role_manager.roles.index',
    ],

    'permission_role_manager.permissions.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.permissions.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
    'permission_role_manager.permissions.create' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.permissions.create',
        'parent' => 'permission_role_manager.permissions.index',
    ],
    'permission_role_manager.permissions.edit' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.permissions.edit',
        'parent' => 'permission_role_manager.permissions.index',
    ],

    'permission_role_manager.users.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.users.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
    'permission_role_manager.users.create' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.users.create',
        'parent' => 'permission_role_manager.users.index',
    ],
    'permission_role_manager.users.edit' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.users.edit',
        'parent' => 'permission_role_manager.users.index',
    ],

    'permission_role_manager.matrix.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.matrix.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
    'permission_role_manager.route_access.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.route_access.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
    'permission_role_manager.inspector.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.inspector.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
    'permission_role_manager.audit.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.audit.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
    'permission_role_manager.settings.index' => [
        'label' => 'permission-role-manager::page_titles.permission_role_manager.settings.index',
        'parent' => 'permission_role_manager.dashboard',
    ],
];
