<div class="pm-stats">
    <div class="pm-stat"><div>{{ __('project-manager::project_manager.total_projects') }}</div><strong>{{ $stats['projects'] ?? 0 }}</strong></div>
    <div class="pm-stat"><div>{{ __('project-manager::project_manager.root_projects') }}</div><strong>{{ $stats['root_projects'] ?? 0 }}</strong></div>
    <div class="pm-stat"><div>{{ __('project-manager::project_manager.total_tasks') }}</div><strong>{{ $stats['tasks'] ?? 0 }}</strong></div>
    <div class="pm-stat"><div>{{ __('project-manager::project_manager.tasks_done') }}</div><strong>{{ $stats['tasks_done'] ?? 0 }}</strong></div>
</div>
