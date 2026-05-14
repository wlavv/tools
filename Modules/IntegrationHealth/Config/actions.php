<?php

return [
    'module_home_routes' => [
        'integration_health' => 'integration_health.index',
    ],

    'routes' => [
        'integration_health.index' => [
            'integrations' => [
                'label' => 'Integrations',
                'route' => 'integration_health.integrations.index',
                'icon' => 'fa-solid fa-plug',
            ],
            'events' => [
                'label' => 'Events',
                'route' => 'integration_health.events.index',
                'icon' => 'fa-solid fa-bell',
            ],
            'new' => 'integration_health.integrations.create',
        ],

        'integration_health.integrations.index' => [
            'back' => 'integration_health.index',
            'new' => 'integration_health.integrations.create',
            'events' => [
                'label' => 'Events',
                'route' => 'integration_health.events.index',
                'icon' => 'fa-solid fa-bell',
            ],
        ],

        'integration_health.integrations.create' => [
            'back' => 'integration_health.integrations.index',
            'save' => true,
        ],

        'integration_health.integrations.edit' => [
            'back' => 'integration_health.integrations.index',
            'save' => true,
            'delete' => false,
            'show' => false,
        ],

        'integration_health.events.index' => [
            'new' => false,
            'back' => 'integration_health.index',
            'integrations' => [
                'label' => 'Integrations',
                'route' => 'integration_health.integrations.index',
                'icon' => 'fa-solid fa-plug',
            ],
        ],
    ],
];
