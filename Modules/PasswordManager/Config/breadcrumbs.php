<?php

return [
    'password_manager.index' => [
        'label' => 'password-manager::breadcrumbs.password_manager',
        'parent' => 'administration.index',
        'translate' => true,
    ],

    'password_manager.create' => [
        'label' => 'password-manager::breadcrumbs.password_manager_new',
        'parent' => 'password_manager.index',
        'translate' => true,
    ],

    'password_manager.show' => [
        'label' => 'password-manager::breadcrumbs.password_manager_show',
        'parent' => 'password_manager.index',
        'translate' => true,
    ],

    'password_manager.edit' => [
        'label' => 'password-manager::breadcrumbs.password_manager_edit',
        'parent' => 'password_manager.index',
        'translate' => true,
    ],
];