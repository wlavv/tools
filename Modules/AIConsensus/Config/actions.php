<?php

return [
    'module_home_routes' => [
        'ai_consensus' => 'ai_consensus.index',
    ],

    'routes' => [
        'ai_consensus.index' => [
            'new' => 'ai_consensus.runs.create',
        ],
        'ai_consensus.runs.index' => [
            'back' => 'ai_consensus.index',
            'new' => 'ai_consensus.runs.create',
        ],
        'ai_consensus.runs.create' => [
            'back' => 'ai_consensus.runs.index',
            'save' => true,
        ],
        'ai_consensus.runs.show' => [
            'back' => 'ai_consensus.runs.index',
        ],
        'ai_consensus.templates.index' => [
            'back' => 'ai_consensus.index',
        ],
        'ai_consensus.templates.edit' => [
            'back' => 'ai_consensus.templates.index',
            'save' => true,
        ],
        'ai_consensus.providers.index' => [
            'back' => 'ai_consensus.index',
        ],
        'ai_consensus.logs.index' => [
            'back' => 'ai_consensus.index',
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
