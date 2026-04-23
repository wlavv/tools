<?php

return [
    'module_home_routes' => [
        'roadmap_manager' => 'roadmap_manager.index',
    ],

    'routes' => [
        'roadmap_manager.index' => [
            'new' => 'roadmap_manager.groups.create',
        ],
        'roadmap_manager.groups.index' => [
            'new' => 'roadmap_manager.groups.create',
        ],
        'roadmap_manager.groups.create' => [
            'back' => 'roadmap_manager.groups.index',
            'save' => true,
        ],
        'roadmap_manager.groups.show' => [
            'back' => 'roadmap_manager.groups.index',
            'edit' => 'roadmap_manager.groups.edit',
        ],
        'roadmap_manager.groups.edit' => [
            'back' => 'roadmap_manager.groups.index',
            'save' => true,
        ],
        'roadmap_manager.projects.index' => [
            'new' => 'roadmap_manager.projects.create',
        ],
        'roadmap_manager.projects.create' => [
            'back' => 'roadmap_manager.projects.index',
            'save' => true,
        ],
        'roadmap_manager.projects.show' => [
            'back' => 'roadmap_manager.projects.index',
            'edit' => 'roadmap_manager.projects.edit',
        ],
        'roadmap_manager.projects.edit' => [
            'back' => 'roadmap_manager.projects.index',
            'save' => true,
        ],
        'roadmap_manager.milestones.index' => [
            'new' => 'roadmap_manager.milestones.create',
        ],
        'roadmap_manager.milestones.create' => [
            'back' => 'roadmap_manager.milestones.index',
            'save' => true,
        ],
        'roadmap_manager.milestones.show' => [
            'back' => 'roadmap_manager.milestones.index',
            'edit' => 'roadmap_manager.milestones.edit',
        ],
        'roadmap_manager.milestones.edit' => [
            'back' => 'roadmap_manager.milestones.index',
            'save' => true,
        ],
        'roadmap_manager.tasks.index' => [
            'new' => 'roadmap_manager.tasks.create',
        ],
        'roadmap_manager.tasks.create' => [
            'back' => 'roadmap_manager.tasks.index',
            'save' => true,
        ],
        'roadmap_manager.tasks.show' => [
            'back' => 'roadmap_manager.tasks.index',
            'edit' => 'roadmap_manager.tasks.edit',
        ],
        'roadmap_manager.tasks.edit' => [
            'back' => 'roadmap_manager.tasks.index',
            'save' => true,
        ],
        'roadmap_manager.tasks.tree' => [
            'back' => 'roadmap_manager.tasks.index',
        ],
        'roadmap_manager.tasks.gantt' => [
            'back' => 'roadmap_manager.tasks.index',
        ],
        'roadmap_manager.tasks.kanban' => [
            'back' => 'roadmap_manager.tasks.index',
        ],
    ],
];
