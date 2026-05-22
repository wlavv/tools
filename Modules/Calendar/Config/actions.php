<?php

return [
    'module_home_routes' => [
        'calendar' => 'calendar.index',
    ],

    'routes' => [
        'calendar.index' => [
            'new' => 'calendar.events.create',
        ],

        'calendar.contexts.index' => [
            'back' => 'calendar.index',
            'new' => [
                'label' => 'New Context',
                'url' => 'javascript:void(0)',
                'icon' => 'fa-solid fa-plus',
                'class' => 'lsg-action-btn lsg-action-btn--success',
                'extra_class' => 'calendar-open-context-modal',
            ],
            'edit' => false,
            'delete' => false,
        ],

        'calendar.categories.index' => [
            'back' => 'calendar.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'calendar.events.index' => [
            'back' => 'calendar.index',
            'new' => 'calendar.events.create',
            'edit' => false,
            'delete' => false,
        ],

        'calendar.events.create' => [
            'back' => 'calendar.events.index',
            'save' => true,
        ],

        'calendar.events.show' => [
            'back' => 'calendar.events.index',
            'new' => 'calendar.events.create',
            'edit' => false,
            'delete' => [
                'route' => 'calendar.events.delete',
                'type' => 'form',
                'method' => 'POST',
                'icon' => 'fa-solid fa-trash',
                'class' => 'lsg-action-btn lsg-action-btn--danger',
                'confirm' => 'Delete this event?',
            ],
        ],

        'calendar.tablet' => [
            'back' => 'calendar.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],
    ],
];
