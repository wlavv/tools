<?php

return [
    'module_home_routes' => [
        'module_health' => 'module_health.index',
    ],

    'routes' => [
        'module_health.index' => [
            'modules' => [
                'label' => 'Modules',
                'route' => 'module_health.modules.index',
                'icon' => 'fa-solid fa-cubes',
            ],
            'profiles' => [
                'label' => 'Profiles',
                'route' => 'module_health.profiles.index',
                'icon' => 'fa-solid fa-sliders',
            ],
            'scans' => [
                'label' => 'Scan History',
                'route' => 'module_health.scans.index',
                'icon' => 'fa-solid fa-clock-rotate-left',
            ],
        ],

        'module_health.modules.index' => [
            'back' => 'module_health.index',
            'profiles' => [
                'label' => 'Profiles',
                'route' => 'module_health.profiles.index',
                'icon' => 'fa-solid fa-sliders',
            ],
            'scans' => [
                'label' => 'Scan History',
                'route' => 'module_health.scans.index',
                'icon' => 'fa-solid fa-clock-rotate-left',
            ],
        ],

        'module_health.modules.show' => [
            'back' => 'module_health.modules.index',
            'modules' => [
                'label' => 'Matrix',
                'route' => 'module_health.modules.index',
                'icon' => 'fa-solid fa-table-cells-large',
            ],
        ],

        'module_health.profiles.index' => [
            'back' => 'module_health.index',
            'modules' => [
                'label' => 'Modules',
                'route' => 'module_health.modules.index',
                'icon' => 'fa-solid fa-cubes',
            ],
        ],

        'module_health.scans.index' => [
            'back' => 'module_health.index',
            'modules' => [
                'label' => 'Modules',
                'route' => 'module_health.modules.index',
                'icon' => 'fa-solid fa-cubes',
            ],
        ],
    ],
];
