<?php

return [
    'productivity_manager.index' => [
        'label' => __('productivity-manager::breadcrumbs.productivity_manager'),
        'parent' => 'administration.index',
        'translate' => false,
    ],
    'productivity_manager.dashboard' => [
        'label' => __('productivity-manager::breadcrumbs.productivity_manager_dashboard'),
        'parent' => 'productivity_manager.index',
        'translate' => false,
    ],
    'productivity_manager.settings' => [
        'label' => __('productivity-manager::breadcrumbs.productivity_manager_settings'),
        'parent' => 'productivity_manager.index',
        'translate' => false,
    ],
];
