<?php

return [
    'notifications.index' => [
        'label' => 'notifications::page_titles.notifications.index',
        'parent' => null,
    ],

    'notifications.create' => [
        'label' => 'notifications::page_titles.notifications.create',
        'parent' => 'notifications.index',
    ],

    'notifications.show' => [
        'label' => 'notifications::page_titles.notifications.show',
        'parent' => 'notifications.index',
        'params' => [
            'notification' => request()->route('notification'),
        ],
    ],

    'notifications.settings' => [
        'label' => 'notifications::page_titles.notifications.settings',
        'parent' => 'notifications.index',
    ],

    'notifications.test' => [
        'label' => 'notifications::page_titles.notifications.test',
        'parent' => 'notifications.index',
    ],
];
