<?php

return [
    'project_manager.index' => [
        'label' => __('project-manager::breadcrumbs.project_manager'),
        'parent' => 'administration.index',
        'translate' => false,
    ],
    'project_manager.create' => [
        'label' => __('project-manager::breadcrumbs.project_manager_new'),
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.show' => [
        'label' => __('project-manager::breadcrumbs.project_manager_show'),
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.edit' => [
        'label' => __('project-manager::breadcrumbs.project_manager_edit'),
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
];
