<?php

return [
    'name' => 'Tasks',
    'slug' => 'tasks',
    'layout' => 'layouts.app',
    'route_prefix' => 'hr/tasks',
    'route_name' => 'tasks.',
    'middleware' => ['web', 'auth'],
    'pagination' => 25,

    'tablet' => [
        'enabled' => true,
        'public_prefix' => 'hub',
        'key' => env('TASKS_TABLET_KEY'),
    ],

    'ui' => [
        'empty_state' => [
            'title' => 'No tasks found',
            'text' => 'Create members and tasks to start planning the family routine.',
        ],
    ],
];
