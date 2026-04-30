<?php

return [
    'module_home_routes' => [
        'notifications' => 'notifications.index',
    ],

    'routes' => [
        'notifications.index' => [
            'new' => [
                'label' => 'Nova notificação',
                'name' => 'Nova notificação',
                'route' => 'notifications.create',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
            'settings' => [
                'label' => 'Config',
                'name' => 'Config',
                'route' => 'notifications.settings',
                'icon' => 'fa-solid fa-cog',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
            ],
            'test' => [
                'label' => 'Testar',
                'name' => 'Testar',
                'route' => 'notifications.test',
                'icon' => 'fa-solid fa-flask',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
        ],

        'notifications.create' => [
            'module_home_route' => 'notifications.index',
            'back' => true,
            'save' => true,
        ],

        'notifications.show' => [
            'module_home_route' => 'notifications.index',
            'back' => true,
            'edit' => false,
            'delete' => [
                'label' => 'Remover',
                'name' => 'Remover',
                'route' => 'notifications.destroy',
                'icon' => 'fa-solid fa-trash',
                'class' => 'lsg-action-btn lsg-action-btn--danger',
                'type' => 'delete',
                'method' => 'DELETE',
                'confirm' => 'Remover esta notificação?',
            ],
        ],

        'notifications.settings' => [
            'module_home_route' => 'notifications.index',
            'back' => true,
            'test' => [
                'label' => 'Testar',
                'name' => 'Testar',
                'route' => 'notifications.test',
                'icon' => 'fa-solid fa-flask',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
        ],

        'notifications.test' => [
            'module_home_route' => 'notifications.index',
            'back' => true,
            'settings' => [
                'label' => 'Config',
                'name' => 'Config',
                'route' => 'notifications.settings',
                'icon' => 'fa-solid fa-cog',
                'class' => 'lsg-action-btn lsg-action-btn--primary',
            ],
        ],
    ],
];
