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

$titles = [
    'project_manager.index' => 'Project Manager',
    'project_manager.dashboard' => 'Project Manager',
    'project_manager.productivity' => 'Productivity Global',
    'project_manager.projects.index' => 'Projetos',
    'project_manager.projects.create' => 'Novo Projeto',
    'project_manager.projects.store' => 'Novo Projeto',
    'project_manager.projects.show' => 'Overview do Projeto',
    'project_manager.projects.overview' => 'Overview do Projeto',
    'project_manager.projects.edit' => 'Editar Projeto',
    'project_manager.projects.update' => 'Editar Projeto',
    'project_manager.projects.roadmap.index' => 'Roadmap',
    'project_manager.projects.productivity' => 'Productivity',
    'project_manager.projects.details' => 'Project Details',
];

foreach ($sections as $key => $label) {
    $base = 'project_manager.projects.' . $key;
    $titles[$base . '.index'] = $label;
    $titles[$base . '.create'] = 'Novo — ' . $label;
    $titles[$base . '.store'] = 'Novo — ' . $label;
    $titles[$base . '.edit'] = 'Editar — ' . $label;
    $titles[$base . '.update'] = 'Editar — ' . $label;
}

return $titles;
