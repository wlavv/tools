<?php

return [
    'name' => 'Budget',
    'slug' => 'budget',
    'layout' => 'layouts.app',
    'route_prefix' => 'budget',
    'route_name' => 'budget.',
    'middleware' => ['web', 'auth'],
    'pagination' => 25,

    'ui' => [
        'empty_state' => [
            'title' => 'No budget data found',
            'text' => 'Add income, expenses or objectives to start tracking this period.',
        ],
    ],

    'reports' => [
        'default_month' => null,
        'default_year' => null,
    ],
];
