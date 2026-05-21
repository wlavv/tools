<?php

$routes = [
        'project_manager.index' => [
            'new' => 'project_manager.projects.create',
            'operations' => [
                'label' => 'Operations',
                'route' => 'project_manager.operations',
                'icon' => 'fa-solid fa-table-columns',
            ],
            'productivity' => [
                'label' => 'Productivity',
                'route' => 'project_manager.productivity',
                'icon' => 'fa-solid fa-table-columns',
            ],
        ],

        'project_manager.dashboard' => [
            'new' => 'project_manager.projects.create',
            'operations' => [
                'label' => 'Operations',
                'route' => 'project_manager.operations',
                'icon' => 'fa-solid fa-table-columns',
            ],
            'productivity' => [
                'label' => 'Productivity',
                'route' => 'project_manager.productivity',
                'icon' => 'fa-solid fa-table-columns',
            ],
        ],

        'project_manager.operations' => [
            'back' => 'project_manager.index',
            'new' => 'project_manager.projects.create',
            'productivity' => [
                'label' => 'Productivity',
                'route' => 'project_manager.productivity',
                'icon' => 'fa-solid fa-gauge-high',
            ],
        ],

        'project_manager.productivity' => [
            'back' => 'project_manager.index',
            'new' => 'project_manager.projects.create',
        ],

        'project_manager.projects.index' => [
            'back' => 'project_manager.index',
            'new' => 'project_manager.projects.create',
        ],

        'project_manager.projects.create' => [
            'back' => 'project_manager.index',
            'save' => true,
            'delete' => false,
            'edit' => false,
        ],

        'project_manager.projects.show' => [
            'back' => 'project_manager.index',
            'delete_route' => 'project_manager.projects.destroy',
            'edit' => 'project_manager.projects.edit',
            'delete' => true,
            'new_task' => [
                'label' => 'Nova Task',
                'route' => 'project_manager.projects.tasks.create',
                'icon' => 'fa-solid fa-plus',
            ],
            'tasks' => [
                'label' => 'Tasks',
                'route' => 'project_manager.projects.tasks.index',
                'icon' => 'fa-solid fa-list-check',
            ],
            'roadmap' => [
                'label' => 'Roadmap',
                'route' => 'project_manager.projects.roadmap.index',
                'icon' => 'fa-solid fa-route',
            ],
            'details' => [
                'label' => 'Project Details',
                'route' => 'project_manager.projects.details',
                'icon' => 'fa-solid fa-sliders',
            ],
        ],

        'project_manager.projects.overview' => [
            'back' => 'project_manager.index',
            'delete_route' => 'project_manager.projects.destroy',
            'edit' => 'project_manager.projects.edit',
            'delete' => 'project_manager.projects.destroy',
            'new_task' => [
                'label' => 'Nova Task',
                'route' => 'project_manager.projects.tasks.create',
                'icon' => 'fa-solid fa-plus',
            ],
            'tasks' => [
                'label' => 'Tasks',
                'route' => 'project_manager.projects.tasks.index',
                'icon' => 'fa-solid fa-list-check',
            ],
            'roadmap' => [
                'label' => 'Roadmap',
                'route' => 'project_manager.projects.roadmap.index',
                'icon' => 'fa-solid fa-route',
            ],
            'details' => [
                'label' => 'Project Details',
                'route' => 'project_manager.projects.details',
                'icon' => 'fa-solid fa-sliders',
            ],
        ],

        'project_manager.projects.edit' => [
            'back' => 'project_manager.projects.show',
            'delete_route' => 'project_manager.projects.destroy',
            'new' => false,
            'show' => false,
            'save' => true,
            'delete' => true,
        ],

        'project_manager.projects.tasks.index' => [
            'back' => 'project_manager.projects.show',
            'new' => 'project_manager.projects.tasks.create',
            'roadmap' => [
                'label' => 'Roadmap',
                'route' => 'project_manager.projects.roadmap.index',
                'icon' => 'fa-solid fa-route',
            ],
            'productivity' => [
                'label' => 'Productivity',
                'route' => 'project_manager.projects.productivity',
                'icon' => 'fa-solid fa-table-columns',
            ],
        ],

        'project_manager.projects.tasks.create' => [
            'back' => 'project_manager.projects.tasks.index',
            'save' => true,
        ],

        'project_manager.projects.tasks.edit' => [
            'back' => 'project_manager.projects.tasks.index',
            'new' => false,
            'show' => false,
            'save' => true,
            'delete' => 'project_manager.projects.tasks.destroy',
        ],

        'project_manager.projects.roadmap.index' => [
            'back' => 'project_manager.projects.show',
            'new' => 'project_manager.projects.tasks.create',
            'tasks' => [
                'label' => 'Tasks',
                'route' => 'project_manager.projects.tasks.index',
                'icon' => 'fa-solid fa-list-check',
            ],
        ],

        'project_manager.projects.roadmap_items.index' => [
            'back' => 'project_manager.projects.show',
            'new' => 'project_manager.projects.roadmap_items.create',
        ],

        'project_manager.projects.roadmap_items.create' => [
            'back' => 'project_manager.projects.roadmap_items.index',
            'save' => true,
        ],

        'project_manager.projects.roadmap_items.edit' => [
            'back' => 'project_manager.projects.roadmap_items.index',
            'new' => false,
            'show' => false,
            'save' => true,
            'delete' => 'project_manager.projects.roadmap_items.destroy',
        ],

        'project_manager.projects.productivity' => [
            'back' => 'project_manager.projects.show',
            'new_task' => [
                'label' => 'Nova Task',
                'route' => 'project_manager.projects.tasks.create',
                'icon' => 'fa-solid fa-plus',
            ],
            'tasks' => [
                'label' => 'Tasks',
                'route' => 'project_manager.projects.tasks.index',
                'icon' => 'fa-solid fa-list-check',
            ],
        ],

        'project_manager.projects.details' => [
            'back' => 'project_manager.projects.show',
            'edit' => 'project_manager.projects.edit',
            'new_asset' => [
                'label' => 'Novo Asset',
                'route' => 'project_manager.projects.assets.create',
                'icon' => 'fa-solid fa-plus',
            ],
        ],
    ];

$detailSections = [
    'modules',
    'design_profiles',
    'design_tokens',
    'assets',
    'technical_stack',
    'environments',
    'guidelines',
    'documentation',
    'decisions',
    'notes',
    'links',
    'task_dependencies',
    'task_blocks',
    'blocks',
    'contacts',
    'external_dependencies',
    'activity',
];

foreach ($detailSections as $section) {
    $base = 'project_manager.projects.' . $section;

    $routes[$base . '.index'] = [
        'back' => 'project_manager.projects.details',
        'new' => $base . '.create',
    ];

    $routes[$base . '.create'] = [
        'back' => 'project_manager.projects.details',
        'save' => true,
    ];

    $routes[$base . '.edit'] = [
        'back' => 'project_manager.projects.details',
        'delete_route' => $base . '.destroy',
        'new' => false,
        'show' => false,
        'save' => true,
        'delete' => $base . '.destroy',
    ];
}

return [
    'module_home_routes' => [
        'project_manager' => 'project_manager.index',
    ],

    'routes' => $routes,
];
