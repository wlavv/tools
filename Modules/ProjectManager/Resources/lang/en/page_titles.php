<?php

$sections = [
    'modules' => 'Modules / Areas',
    'design_profiles' => 'Visual Identity',
    'design_tokens' => 'Design Tokens',
    'assets' => 'Assets',
    'technical_stack' => 'Technical Stack',
    'environments' => 'Environments',
    'guidelines' => 'Technical Guidelines',
    'documentation' => 'Documentation',
    'decisions' => 'Decisions',
    'notes' => 'Notes',
    'links' => 'Useful Links',
    'roadmap_items' => 'Roadmap',
    'tasks' => 'Tasks',
    'task_dependencies' => 'Dependencies',
    'task_blocks' => 'Blocks',
    'blocks' => 'Content Blocks',
    'contacts' => 'Contacts',
    'external_dependencies' => 'External Dependencies',
    'activity' => 'Activity Log',
];

$titles = [
    'project_manager.index' => 'Project Manager',
    'project_manager.dashboard' => 'Project Manager',
    'project_manager.productivity' => 'Global Productivity',
    'project_manager.projects.index' => 'Projects',
    'project_manager.projects.create' => 'New Project',
    'project_manager.projects.store' => 'New Project',
    'project_manager.projects.show' => 'Project Overview',
    'project_manager.projects.overview' => 'Project Overview',
    'project_manager.projects.edit' => 'Edit Project',
    'project_manager.projects.update' => 'Edit Project',
    'project_manager.projects.roadmap.index' => 'Roadmap',
    'project_manager.projects.productivity' => 'Productivity',
    'project_manager.projects.details' => 'Project Details',
];

foreach ($sections as $key => $label) {
    $base = 'project_manager.projects.' . $key;
    $titles[$base . '.index'] = $label;
    $titles[$base . '.create'] = 'New - ' . $label;
    $titles[$base . '.store'] = 'New - ' . $label;
    $titles[$base . '.edit'] = 'Edit - ' . $label;
    $titles[$base . '.update'] = 'Edit - ' . $label;
}

return $titles;
