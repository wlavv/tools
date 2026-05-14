<?php

return [
    'module_home_routes' => [
        'job_queue_monitor' => 'job_queue_monitor.index',
    ],

    'routes' => [
        'job_queue_monitor.index' => [
            'new' => false,
            'failed' => [
                'label' => 'Failed',
                'route' => 'job_queue_monitor.failed.index',
                'icon' => 'fa-solid fa-triangle-exclamation',
                'class' => 'lsg-action-btn lsg-action-btn--danger',
            ],
            'health' => [
                'label' => 'Health',
                'route' => 'job_queue_monitor.health.index',
                'icon' => 'fa-solid fa-heart-pulse',
            ],
            'settings' => [
                'label' => 'Settings',
                'route' => 'job_queue_monitor.settings.index',
                'icon' => 'fa-solid fa-cog',
            ],
        ],

        'job_queue_monitor.failed.index' => [
            'new' => false,
            'back' => 'job_queue_monitor.index',
            'health' => [
                'label' => 'Health',
                'route' => 'job_queue_monitor.health.index',
                'icon' => 'fa-solid fa-heart-pulse',
            ],
        ],

        'job_queue_monitor.health.index' => [
            'new' => false,
            'back' => 'job_queue_monitor.index',
            'failed' => [
                'label' => 'Failed',
                'route' => 'job_queue_monitor.failed.index',
                'icon' => 'fa-solid fa-triangle-exclamation',
                'class' => 'lsg-action-btn lsg-action-btn--danger',
            ],
        ],

        'job_queue_monitor.settings.index' => [
            'new' => false,
            'back' => 'job_queue_monitor.index',
        ],

        'job_queue_monitor.show' => [
            'back' => 'job_queue_monitor.index',
            'edit' => false,
            'delete' => false,
        ],
    ],
];
