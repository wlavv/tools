<?php
return [
    'layout' => 'layouts.app',
    'project_statuses' => [
        'new' => 'New',
        'in_progress' => 'In Progress',
        'waiting_info' => 'Waiting Info',
        'hold' => 'Hold',
        'done' => 'Done',
        'cancelled' => 'Cancelled',
    ],

    'task_statuses' => [
        0 => 'Pending',
        1 => 'Active',
        2 => 'Blocked',
        3 => 'Done',
        4 => 'Cancelled',
    ],

    'task_priorities' => [
        1 => 'Critical',
        2 => 'High',
        3 => 'Normal',
        4 => 'Low',
        5 => 'Backlog',
    ],

    'task_types' => [
        'feature' => 'Feature',
        'bug' => 'Bug',
        'refactor' => 'Refactor',
        'research' => 'Research',
        'content' => 'Content',
        'admin' => 'Admin',
        'operation' => 'Operation',
    ],
];
