<?php

return [
    'external_enabled' => env('SYSTEM_TOOLS_EXTERNAL_ENABLED', false),
    'external_token' => env('SYSTEM_TOOLS_EXTERNAL_TOKEN'),

    'audit_enabled' => env('SYSTEM_TOOLS_AUDIT_ENABLED', true),
    'max_log_lines' => env('SYSTEM_TOOLS_MAX_LOG_LINES', 250),

    'risk_labels' => [
        'safe' => 'Seguro',
        'medium' => 'Médio',
        'danger' => 'Crítico',
    ],

    'sections' => [
        'cache' => [
            'label' => 'Cache & Optimize',
            'description' => 'Limpeza e reconstrução de cache Laravel.',
            'icon' => 'fa-solid fa-broom',
        ],
        'git' => [
            'label' => 'Git & Deploy',
            'description' => 'Estado do repositório, deploy e recuperação controlada.',
            'icon' => 'fa-brands fa-git-alt',
        ],
        'logs' => [
            'label' => 'Logs & Diagnostics',
            'description' => 'Consulta de logs Laravel/PHP e diagnóstico rápido.',
            'icon' => 'fa-solid fa-file-lines',
        ],
        'routes' => [
            'label' => 'Routes & Structure Viewer',
            'description' => 'Consulta textual/gráfica da estrutura de rotas.',
            'icon' => 'fa-solid fa-route',
        ],
        'modules' => [
            'label' => 'Modules Manager',
            'description' => 'Inspeção dos módulos, manifests, providers e estrutura.',
            'icon' => 'fa-solid fa-cubes',
        ],
        'database' => [
            'label' => 'Database',
            'description' => 'Migrations, ligações, tabelas e tamanhos.',
            'icon' => 'fa-solid fa-database',
        ],
        'queue' => [
            'label' => 'Queue & Jobs',
            'description' => 'Gestão de queues, workers e jobs falhados.',
            'icon' => 'fa-solid fa-rotate',
        ],
        'scheduler' => [
            'label' => 'Scheduler & Cron',
            'description' => 'Consulta e execução manual do scheduler.',
            'icon' => 'fa-solid fa-clock',
        ],
        'security' => [
            'label' => 'Security & Maintenance',
            'description' => 'Maintenance mode, permissões e exposição de ficheiros sensíveis.',
            'icon' => 'fa-solid fa-shield-halved',
        ],
        'storage' => [
            'label' => 'Storage & Files',
            'description' => 'Storage link, pastas graváveis e utilização de espaço.',
            'icon' => 'fa-solid fa-folder-tree',
        ],
        'assets' => [
            'label' => 'Assets / Build',
            'description' => 'Reconstrução dos assets públicos geridos pelo Vite.',
            'icon' => 'fa-solid fa-cubes',
        ],
        'prestashop' => [
            'label' => 'PrestaShop',
            'description' => 'Diagnósticos orientados à ligação mysql2 / PrestaShop.',
            'icon' => 'fa-solid fa-store',
        ],
        'streamdeck' => [
            'label' => 'Stream Deck / Remote Actions',
            'description' => 'Links remotos seguros para ações permitidas.',
            'icon' => 'fa-solid fa-keyboard',
        ],
        'maps' => [
            'label' => 'Graphical System Maps',
            'description' => 'Mapas textuais para compreensão da estrutura e fluxos.',
            'icon' => 'fa-solid fa-diagram-project',
        ],
    ],
];
