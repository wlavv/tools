<?php

return [
    'password_manager.index' => [
        'label' => __('password-manager::breadcrumbs.password_manager'),
        'parent' => 'administration.index',
        'translate' => false,
    ],
    'password_manager.create' => [
        'label' => __('password-manager::breadcrumbs.password_manager_new'),
        'parent' => 'password_manager.index',
        'translate' => false,
    ],
    'password_manager.show' => [
        'label' => __('password-manager::breadcrumbs.password_manager_show'),
        'parent' => 'password_manager.index',
        'translate' => false,
    ],
    'password_manager.edit' => [
        'label' => __('password-manager::breadcrumbs.password_manager_edit'),
        'parent' => 'password_manager.index',
        'translate' => false,
    ],
];