<?php

return [
    'module_home_routes' => [
        'ai_consensus' => 'ai_consensus.index',
    ],

    'routes' => [
        'ai_consensus.index' => [
            'new' => 'ai_consensus.create',
        ],
        'ai_consensus.create' => [
            'back' => 'ai_consensus.index',
            'save' => true,
        ],
        'ai_consensus.show' => [
            'back' => 'ai_consensus.index',
            'edit' => 'ai_consensus.edit',
            'delete' => true,
            'new' => 'ai_consensus.create',
        ],
        'ai_consensus.edit' => [
            'back' => 'ai_consensus.index',
            'save' => true,
            'new' => 'ai_consensus.create',
            'delete' => true,
        ],
    ],
];
