<?php

return [
    'name' => 'Calendar',
    'slug' => 'calendar',
    'layout' => 'layouts.app',
    'route_prefix' => 'hr/calendar',
    'route_name' => 'calendar.',
    'middleware' => ['web', 'auth'],
    'pagination' => 25,

    'ui' => [
        'empty_state' => [
            'title' => 'No calendar records found',
            'text' => 'Create contexts, categories or events to start using the calendar.',
        ],
    ],

    'tablet' => [
        'enabled' => true,
        'default_context' => null,
    ],
];
