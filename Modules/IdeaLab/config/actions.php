<?php

return [
    'module_home_routes' => [
        'idealab' => 'idealab.index',
    ],

    'routes' => [
        'idealab.index' => [
            'new' => [
                'label' => 'New Idea',
                'route' => 'idealab.create',
                'icon' => 'fa-solid fa-plus',
            ],
            'templates' => [
                'label' => 'AI Templates',
                'route' => 'idealab.templates.index',
                'icon' => 'fa-solid fa-cog',
            ],
        ],

        'idealab.create' => [
            'back' => 'idealab.index',
            'save' => true,
        ],

        'idealab.show' => [
            'back' => 'idealab.index',
            'edit' => 'idealab.edit',
            'convert' => [
                'label' => 'Convert to Project',
                'route' => 'idealab.convert',
                'icon' => 'fa-solid fa-diagram-project',
                'type' => 'form',
                'method' => 'POST',
                'class' => 'lsg-action-btn lsg-action-btn--success',
            ],
        ],

        'idealab.edit' => [
            'back' => 'idealab.index',
            'save' => true,
            'show' => 'idealab.show',
        ],

        'idealab.templates.index' => [
            'back' => 'idealab.index',
            'new' => [
                'label' => 'New Template',
                'route' => 'idealab.templates.create',
                'icon' => 'fa-solid fa-plus',
            ],
        ],

        'idealab.templates.create' => [
            'back' => 'idealab.templates.index',
            'save' => true,
        ],

        'idealab.templates.edit' => [
            'back' => 'idealab.templates.index',
            'save' => true,
        ],
    ],
];
