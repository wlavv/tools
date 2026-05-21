<?php

$sections = [
    'modules' => 'Módulos / Áreas',
    'design_profiles' => 'Identidade Visual',
    'design_tokens' => 'Design Tokens',
    'assets' => 'Assets',
    'technical_stack' => 'Stack Técnica',
    'environments' => 'Ambientes',
    'guidelines' => 'Guidelines Técnicas',
    'documentation' => 'Documentação',
    'decisions' => 'Decisões',
    'notes' => 'Notas',
    'links' => 'Links Úteis',
    'roadmap_items' => 'Roadmap',
    'tasks' => 'Tasks',
    'task_dependencies' => 'Dependências',
    'task_blocks' => 'Bloqueios',
    'blocks' => 'Blocos de Conteúdo',
    'contacts' => 'Contactos',
    'external_dependencies' => 'Dependências Externas',
    'activity' => 'Activity Log',
];

$breadcrumbs = [
    'project_manager.index' => [
        'label' => 'Project Manager',
        'parent' => 'web.index',
        'translate' => false,
    ],
    'project_manager.dashboard' => [
        'label' => 'Dashboard',
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.operations' => [
        'label' => 'Operations',
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.productivity' => [
        'label' => 'Productivity Global',
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.projects.index' => [
        'label' => 'Projetos',
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.projects.create' => [
        'label' => 'Novo Projeto',
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.projects.show' => [
        'label' => 'Overview',
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.projects.overview' => [
        'label' => 'Overview',
        'parent' => 'project_manager.index',
        'translate' => false,
    ],
    'project_manager.projects.edit' => [
        'label' => 'Editar Projeto',
        'parent' => 'project_manager.projects.show',
        'translate' => false,
    ],
    'project_manager.projects.roadmap.index' => [
        'label' => 'Roadmap',
        'parent' => 'project_manager.projects.show',
        'translate' => false,
    ],
    'project_manager.projects.productivity' => [
        'label' => 'Productivity',
        'parent' => 'project_manager.projects.show',
        'translate' => false,
    ],
    'project_manager.projects.details' => [
        'label' => 'Project Details',
        'parent' => 'project_manager.projects.show',
        'translate' => false,
    ],
];

foreach ($sections as $key => $label) {
    $parent = in_array($key, ['tasks', 'task_dependencies', 'task_blocks', 'roadmap_items'], true)
        ? 'project_manager.projects.show'
        : 'project_manager.projects.details';

    $base = 'project_manager.projects.' . $key;

    $breadcrumbs[$base . '.index'] = [
        'label' => $label,
        'parent' => $parent,
        'translate' => false,
    ];

    $breadcrumbs[$base . '.create'] = [
        'label' => 'Novo — ' . $label,
        'parent' => $base . '.index',
        'translate' => false,
    ];

    $breadcrumbs[$base . '.edit'] = [
        'label' => 'Editar — ' . $label,
        'parent' => $base . '.index',
        'translate' => false,
    ];
}

return $breadcrumbs;
