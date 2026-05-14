<?php

return [
    'name' => 'Config Inspector',
    'slug' => 'config-inspector',
    'route_prefix' => 'settings/config-inspector',
    'route_name' => 'config_inspector',

    'severity_order' => [
        'critical' => 5,
        'error' => 4,
        'warning' => 3,
        'info' => 2,
        'success' => 1,
    ],

    'inspectors' => [
        'overview' => [
            'label' => 'Overview',
            'icon' => 'fa-solid fa-gauge-high',
            'class' => Modules\ConfigInspector\Inspectors\OverviewInspector::class,
            'description' => 'Resumo rápido do estado global do sistema.',
        ],
        'environment' => [
            'label' => 'Environment',
            'icon' => 'fa-solid fa-server',
            'class' => Modules\ConfigInspector\Inspectors\EnvironmentInspector::class,
            'description' => 'APP, cache, session, queue, mail e variáveis relevantes.',
        ],
        'modules' => [
            'label' => 'Modules',
            'icon' => 'fa-solid fa-cubes',
            'class' => Modules\ConfigInspector\Inspectors\ModuleInspector::class,
            'description' => 'Manifest, provider, estrutura base e regras do modelo modular LSG.',
        ],
        'routes' => [
            'label' => 'Routes',
            'icon' => 'fa-solid fa-route',
            'class' => Modules\ConfigInspector\Inspectors\RouteInspector::class,
            'description' => 'Rotas, names, controllers, middleware e duplicados.',
        ],
        'translations' => [
            'label' => 'Translations',
            'icon' => 'fa-solid fa-language',
            'class' => Modules\ConfigInspector\Inspectors\TranslationInspector::class,
            'description' => 'Namespaces, ficheiros lang e chaves comuns do módulo.',
        ],
        'breadcrumbs_actions' => [
            'label' => 'Breadcrumbs & Actions',
            'icon' => 'fa-solid fa-sitemap',
            'class' => Modules\ConfigInspector\Inspectors\BreadcrumbActionInspector::class,
            'description' => 'Configurações de breadcrumbs, page titles e actions.',
        ],
        'database' => [
            'label' => 'Database',
            'icon' => 'fa-solid fa-database',
            'class' => Modules\ConfigInspector\Inspectors\DatabaseInspector::class,
            'description' => 'Ligações mysql/mysql2 e migrations.',
        ],
        'storage' => [
            'label' => 'Storage',
            'icon' => 'fa-solid fa-folder-tree',
            'class' => Modules\ConfigInspector\Inspectors\StorageInspector::class,
            'description' => 'Permissões e diretórios críticos.',
        ],
        'security' => [
            'label' => 'Security',
            'icon' => 'fa-solid fa-shield-halved',
            'class' => Modules\ConfigInspector\Inspectors\SecurityInspector::class,
            'description' => 'Riscos comuns de produção e exposição de configuração.',
        ],
    ],

    'module_required_manifest_keys' => [
        'name', 'slug', 'enabled', 'version', 'provider'
    ],

    'module_expected_paths' => [
        'Config/config.php',
        'Config/actions.php',
        'Config/breadcrumbs.php',
        'Config/page_titles.php',
        'Routes/web.php',
        'Resources/views',
        'Resources/lang',
    ],
];
