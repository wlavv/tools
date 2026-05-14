<?php

return [
    'group' => 'Permission / Role Manager',
    'permissions' => [
        'permission_manager.view' => [
            'label' => 'Ver Permission Manager',
            'description' => 'Pode aceder ao módulo Permission / Role Manager.',
            'risk' => 'medium',
        ],
        'permission_manager.roles.manage' => [
            'label' => 'Gerir Roles',
            'description' => 'Pode criar, editar e desativar roles.',
            'risk' => 'high',
        ],
        'permission_manager.permissions.manage' => [
            'label' => 'Gerir Permissions',
            'description' => 'Pode criar, editar e sincronizar permissões.',
            'risk' => 'critical',
        ],
        'permission_manager.users.manage' => [
            'label' => 'Gerir acessos de utilizadores',
            'description' => 'Pode alterar roles e permissões diretas de utilizadores.',
            'risk' => 'critical',
        ],
        'permission_manager.route_access.manage' => [
            'label' => 'Gerir acesso por rotas',
            'description' => 'Pode descobrir rotas do B.O. e gerar permissions/roles por modulo.',
            'risk' => 'critical',
        ],
        'permission_manager.audit.view' => [
            'label' => 'Ver Audit Log',
            'description' => 'Pode consultar histórico de alterações de permissões.',
            'risk' => 'high',
        ],
    ],
];
