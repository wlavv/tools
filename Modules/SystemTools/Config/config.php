<?php

return [
    'external_enabled' => env('SYSTEM_TOOLS_EXTERNAL_ENABLED', false),
    'external_token' => env('SYSTEM_TOOLS_EXTERNAL_TOKEN'),

    'risk_labels' => [
        'safe' => 'Seguro',
        'medium' => 'Médio',
        'danger' => 'Crítico',
    ],

    'sections' => [
        'cache' => [
            'label' => 'Cache / Otimização',
            'description' => 'Limpeza e reconstrução de cache Laravel.',
            'icon' => 'fa-solid fa-broom',
        ],
        'database' => [
            'label' => 'Base de Dados',
            'description' => 'Migrations e estado da base de dados.',
            'icon' => 'fa-solid fa-database',
        ],
        'queue' => [
            'label' => 'Queues / Jobs',
            'description' => 'Gestão de workers e jobs.',
            'icon' => 'fa-solid fa-rotate',
        ],
        'storage' => [
            'label' => 'Storage',
            'description' => 'Links e verificações de storage.',
            'icon' => 'fa-solid fa-folder-tree',
        ],
        'diagnostics' => [
            'label' => 'Diagnóstico',
            'description' => 'Verificações rápidas do ambiente.',
            'icon' => 'fa-solid fa-stethoscope',
        ],
    ],
];
