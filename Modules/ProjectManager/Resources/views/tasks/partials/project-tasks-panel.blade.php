@php
    $taskQuery = \Modules\ProjectManager\Models\ProjectTask::forProject($project->id)
        ->root()
        ->with('childrenRecursive')
        ->orderBy('execution_order')
        ->orderByDesc('priority')
        ->orderBy('id');

    if (\Modules\ProjectManager\Models\ProjectTask::dependenciesTableReady()) {
        $taskQuery->with('dependencies');
    }

    $taskTree = $taskQuery->get();
@endphp

<div class="pm-card pm-tasks-panel mt-3">
    <div class="pm-section-header">
        <div>
            <div class="pm-kicker">{{ __('project-manager::tasks.tasks') }}</div>
            <h5 class="mb-0">{{ __('project-manager::tasks.tasks') }}</h5>
        </div>
        <a href="{{ route('project_manager.tasks.create', $project) }}" class="lsg-action-btn lsg-action-btn--success">
            <span class="lsg-action-btn__icon"><i class="fa-solid fa-plus"></i></span>
            <span>{{ __('project-manager::tasks.new_task') }}</span>
        </a>
    </div>

    @unless(\Modules\ProjectManager\Models\ProjectTask::dependenciesTableReady())
        <div class="pm-schema-warning mb-3">
            <i class="fa-solid fa-triangle-exclamation"></i>
            A tabela <strong>wt_todo_dependencies</strong> ainda não tem as colunas normalizadas
            <strong>todo_id</strong> e <strong>depends_on_todo_id</strong>. Corre <code>php artisan migrate</code> para ativar dependências.
        </div>
    @endunless

    @include('project-manager::tasks.partials.task-tree', [
        'tasks' => $taskTree,
        'project' => $project,
        'level' => 0,
    ])
</div>
