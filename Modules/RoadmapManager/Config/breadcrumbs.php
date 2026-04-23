<?php

return [
    'roadmap_manager.index' => [
        'label' => __('roadmap-manager::breadcrumbs.roadmap_manager'),
        'parent' => 'administration.index',
        'translate' => false,
    ],
    'roadmap_manager.groups.index' => [
        'label' => __('roadmap-manager::breadcrumbs.groups'),
        'parent' => 'roadmap_manager.index',
        'translate' => false,
    ],
    'roadmap_manager.groups.create' => [
        'label' => __('roadmap-manager::breadcrumbs.groups_new'),
        'parent' => 'roadmap_manager.groups.index',
        'translate' => false,
    ],
    'roadmap_manager.groups.show' => [
        'label' => __('roadmap-manager::breadcrumbs.groups_show'),
        'parent' => 'roadmap_manager.groups.index',
        'translate' => false,
    ],
    'roadmap_manager.groups.edit' => [
        'label' => __('roadmap-manager::breadcrumbs.groups_edit'),
        'parent' => 'roadmap_manager.groups.index',
        'translate' => false,
    ],
    'roadmap_manager.projects.index' => [
        'label' => __('roadmap-manager::breadcrumbs.projects'),
        'parent' => 'roadmap_manager.index',
        'translate' => false,
    ],
    'roadmap_manager.projects.create' => [
        'label' => __('roadmap-manager::breadcrumbs.projects_new'),
        'parent' => 'roadmap_manager.projects.index',
        'translate' => false,
    ],
    'roadmap_manager.projects.show' => [
        'label' => __('roadmap-manager::breadcrumbs.projects_show'),
        'parent' => 'roadmap_manager.projects.index',
        'translate' => false,
    ],
    'roadmap_manager.projects.edit' => [
        'label' => __('roadmap-manager::breadcrumbs.projects_edit'),
        'parent' => 'roadmap_manager.projects.index',
        'translate' => false,
    ],
    'roadmap_manager.milestones.index' => [
        'label' => __('roadmap-manager::breadcrumbs.milestones'),
        'parent' => 'roadmap_manager.index',
        'translate' => false,
    ],
    'roadmap_manager.milestones.create' => [
        'label' => __('roadmap-manager::breadcrumbs.milestones_new'),
        'parent' => 'roadmap_manager.milestones.index',
        'translate' => false,
    ],
    'roadmap_manager.milestones.show' => [
        'label' => __('roadmap-manager::breadcrumbs.milestones_show'),
        'parent' => 'roadmap_manager.milestones.index',
        'translate' => false,
    ],
    'roadmap_manager.milestones.edit' => [
        'label' => __('roadmap-manager::breadcrumbs.milestones_edit'),
        'parent' => 'roadmap_manager.milestones.index',
        'translate' => false,
    ],
    'roadmap_manager.tasks.index' => [
        'label' => __('roadmap-manager::breadcrumbs.tasks'),
        'parent' => 'roadmap_manager.index',
        'translate' => false,
    ],
    'roadmap_manager.tasks.create' => [
        'label' => __('roadmap-manager::breadcrumbs.tasks_new'),
        'parent' => 'roadmap_manager.tasks.index',
        'translate' => false,
    ],
    'roadmap_manager.tasks.show' => [
        'label' => __('roadmap-manager::breadcrumbs.tasks_show'),
        'parent' => 'roadmap_manager.tasks.index',
        'translate' => false,
    ],
    'roadmap_manager.tasks.edit' => [
        'label' => __('roadmap-manager::breadcrumbs.tasks_edit'),
        'parent' => 'roadmap_manager.tasks.index',
        'translate' => false,
    ],
    'roadmap_manager.tasks.tree' => [
        'label' => __('roadmap-manager::breadcrumbs.tasks_tree'),
        'parent' => 'roadmap_manager.tasks.index',
        'translate' => false,
    ],
    'roadmap_manager.tasks.gantt' => [
        'label' => __('roadmap-manager::breadcrumbs.tasks_gantt'),
        'parent' => 'roadmap_manager.tasks.index',
        'translate' => false,
    ],
    'roadmap_manager.tasks.kanban' => [
        'label' => __('roadmap-manager::breadcrumbs.tasks_kanban'),
        'parent' => 'roadmap_manager.tasks.index',
        'translate' => false,
    ],
];
