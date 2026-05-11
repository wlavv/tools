<?php

return [
    'module_home_routes' => [
        'investments' => 'investments.index',
    ],

    'routes' => [
        'investments.index' => ['new' => false],
        'investments.assets.index' => [
            'new' => 'investments.assets.create',
        ],
        'investments.assets.create' => [
            'back' => 'investments.assets.index',
            'save' => true,
        ],
        'investments.broker_accounts.index' => [
            'new' => 'investments.broker_accounts.create',
        ],
        'investments.broker_accounts.create' => [
            'back' => 'investments.broker_accounts.index',
            'save' => true,
        ],
        'investments.broker_accounts.edit' => [
            'back' => 'investments.broker_accounts.index',
            'save' => true,
        ],
        'investments.positions.index' => [
            'new' => 'investments.positions.create',
        ],
        'investments.positions.create' => [
            'back' => 'investments.positions.index',
            'save' => true,
        ],
        'investments.positions.show' => [
            'back' => 'investments.positions.index',
            'delete' => false,
            'edit' => false,
            'new' => false,
        ],
    ],
];
