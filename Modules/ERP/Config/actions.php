<?php

return [
    'module_home_routes' => [
        'erp' => 'erp.dashboard',
    ],

    'routes' => [
        'erp.dashboard' => [
            'new_order_note' => [
                'key' => 'new_order_note',
                'label' => 'Nova Order Note',
                'name' => 'Nova Order Note',
                'icon' => 'fa-solid fa-plus',
                'route' => 'erp.dashboard',
                'type' => 'link',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
            'settings' => [
                'key' => 'settings',
                'label' => 'Settings',
                'name' => 'Settings',
                'icon' => 'fa-solid fa-cog',
                'route' => 'erp.settings.index',
                'type' => 'link',
                'class' => 'lsg-action-btn lsg-action-btn--gold',
            ],
        ],

        'erp.timeline' => [
            'new_order_note' => [
                'key' => 'new_order_note',
                'label' => 'Nova Order Note',
                'name' => 'Nova Order Note',
                'icon' => 'fa-solid fa-plus',
                'route' => 'erp.dashboard',
                'type' => 'link',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
            'settings' => [
                'key' => 'settings',
                'label' => 'Settings',
                'name' => 'Settings',
                'icon' => 'fa-solid fa-cog',
                'route' => 'erp.settings.index',
                'type' => 'link',
                'class' => 'lsg-action-btn lsg-action-btn--gold',
            ],
        ],

        'erp.settings.index' => [
            'back' => 'erp.dashboard',
        ],

        'erp.settings.document-types.index' => [
            'back' => 'erp.settings.index',
        ],
        'erp.settings.statuses.index' => [
            'back' => 'erp.settings.index',
        ],
        'erp.settings.workflows.index' => [
            'back' => 'erp.settings.index',
        ],
        'erp.settings.supplier-terms.index' => [
            'back' => 'erp.settings.index',
        ],

        'erp.settings.document-types.create' => [
            'back' => 'erp.settings.document-types.index',
            'save' => true,
        ],
        'erp.settings.statuses.create' => [
            'back' => 'erp.settings.statuses.index',
            'save' => true,
        ],
        'erp.settings.workflows.create' => [
            'back' => 'erp.settings.workflows.index',
            'save' => true,
        ],
        'erp.settings.supplier-terms.create' => [
            'back' => 'erp.settings.supplier-terms.index',
            'save' => true,
        ],
    ],
];
