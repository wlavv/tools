<?php

return [
    'route_prefix' => 'project-manager',
    'route_name' => 'project_manager',
    'layout' => 'layouts.app',
    'tables' => [
        'projects' => 'wt_projects',
        'tasks' => 'wt_todo',
    ],
    'project_statuses' => [
        'new' => 'New',
        'in_progress' => 'In Progress',
        'waiting_info' => 'Waiting Info',
        'hold' => 'Hold',
        'done' => 'Done',
        'cancelled' => 'Cancelled',
    ],
    'task_statuses' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'done' => 'Done',
        'blocked' => 'Blocked',
    ],
];
