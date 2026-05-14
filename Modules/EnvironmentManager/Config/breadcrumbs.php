<?php

return [
    'environment_manager.index' => [
        'label' => 'environment-manager::breadcrumbs.environment_manager',
        'parent' => 'settings.index',
        'translate' => true,
    ],

    'environment_manager.env' => [
        'label' => 'environment-manager::breadcrumbs.environment_env',
        'parent' => 'environment_manager.index',
        'translate' => true,
    ],

    'environment_manager.config' => [
        'label' => 'environment-manager::breadcrumbs.environment_config',
        'parent' => 'environment_manager.index',
        'translate' => true,
    ],

    'environment_manager.modules' => [
        'label' => 'environment-manager::breadcrumbs.environment_modules',
        'parent' => 'environment_manager.index',
        'translate' => true,
    ],

    'environment_manager.modules.show' => [
        'label' => 'environment-manager::breadcrumbs.environment_module_show',
        'parent' => 'environment_manager.modules',
        'translate' => true,
    ],

    'environment_manager.effective' => [
        'label' => 'environment-manager::breadcrumbs.environment_effective',
        'parent' => 'environment_manager.index',
        'translate' => true,
    ],
];
