<?php

return [
    'name' => 'Permission / Role Manager',
    'slug' => 'permission-role-manager',
    'route_prefix' => 'settings/permission-role-manager',
    'route_name' => 'permission_role_manager.',
    'layout' => 'permission-role-manager::layouts.module',
    'user_model' => env('PERMISSION_USER_MODEL', 'App\\Models\\User'),
    'audit_enabled' => true,
    'direct_user_permissions_enabled' => false,
    'route_access_enforcement_enabled' => env('PERMISSION_ROUTE_ACCESS_ENABLED', true),
    'route_access_super_user_ids' => array_filter(array_map('intval', explode(',', env('PERMISSION_ROUTE_ACCESS_SUPER_USER_IDS', '')))),
    'risk_levels' => [
        'low' => 'Baixo',
        'medium' => 'Médio',
        'high' => 'Alto',
        'critical' => 'Crítico',
    ],
];
