<?php

return [
    'module_health.index' => [
        'label' => 'module-health::page_titles.index',
        'parent' => 'settings.index',
    ],

    'module_health.modules.index' => [
        'label' => 'module-health::page_titles.modules.index',
        'parent' => 'module_health.index',
    ],

    'module_health.modules.show' => [
        'label' => 'module-health::page_titles.modules.show',
        'parent' => 'module_health.modules.index',
    ],

    'module_health.profiles.index' => [
        'label' => 'module-health::page_titles.profiles.index',
        'parent' => 'module_health.index',
    ],

    'module_health.scans.index' => [
        'label' => 'module-health::page_titles.scans.index',
        'parent' => 'module_health.index',
    ],
];
