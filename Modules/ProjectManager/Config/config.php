<?php

return [
    'name' => 'Project Manager',
    'slug' => 'project-manager',
    'route_prefix' => 'project-manager',
    'route_name' => 'project_manager.',
    'middleware' => ['web', 'auth'],
    'layout' => 'layouts.app',
    'tables' => [
        'projects' => 'wt_projects',
    ],
];
