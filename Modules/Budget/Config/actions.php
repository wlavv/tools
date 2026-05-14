<?php

return [
    'module_home_routes' => [
        'budget' => 'budget.index',
    ],

    'routes' => [
        'budget.index' => [
            'new' => false,
        ],

        'budget.reports.category' => [
            'back' => 'budget.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'budget.reports.subcategory' => [
            'back' => 'budget.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],

        'budget.reports.annual' => [
            'back' => 'budget.index',
            'new' => false,
            'edit' => false,
            'delete' => false,
        ],
    ],
];
