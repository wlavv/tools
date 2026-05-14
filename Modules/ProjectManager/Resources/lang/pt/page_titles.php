<?php

$sections = [
    'modules' => 'Modulos / Areas',
    'design_profiles' => 'Identidade Visual',
    'design_tokens' => 'Design Tokens',
    'assets' => 'Assets',
    'technical_stack' => 'Stack Tecnica',
    'environments' => 'Ambientes',
    'guidelines' => 'Guidelines Tecnicas',
    'documentation' => 'Documentacao',
    'decisions' => 'Decisoes',
    'notes' => 'Notas',
    'links' => 'Links Uteis',
    'roadmap_items' => 'Roadmap',
    'tasks' => 'Tasks',
    'task_dependencies' => 'Dependencias',
    'task_blocks' => 'Bloqueios',
    'blocks' => 'Blocos de Conteudo',
    'contacts' => 'Contactos',
    'external_dependencies' => 'Dependencias Externas',
    'activity' => 'Activity Log',
];

$titles = [
    'project_manager.index' => 'Project Manager',
    'project_manager.dashboard' => 'Project Manager',
    'project_manager.productivity' => 'Produtividade Global',
    'project_manager.projects.index' => 'Projetos',
    'project_manager.projects.create' => 'Novo Projeto',
    'project_manager.projects.store' => 'Novo Projeto',
    'project_manager.projects.show' => 'Overview do Projeto',
    'project_manager.projects.overview' => 'Overview do Projeto',
    'project_manager.projects.edit' => 'Editar Projeto',
    'project_manager.projects.update' => 'Editar Projeto',
    'project_manager.projects.roadmap.index' => 'Roadmap',
    'project_manager.projects.productivity' => 'Produtividade',
    'project_manager.projects.details' => 'Detalhes do Projeto',
];

foreach ($sections as $key => $label) {
    $base = 'project_manager.projects.' . $key;
    $titles[$base . '.index'] = $label;
    $titles[$base . '.create'] = 'Novo - ' . $label;
    $titles[$base . '.store'] = 'Novo - ' . $label;
    $titles[$base . '.edit'] = 'Editar - ' . $label;
    $titles[$base . '.update'] = 'Editar - ' . $label;
}

return $titles;
