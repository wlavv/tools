<div class="pm-group is-open">
    <div class="pm-group-head" data-group-toggle>
        <div class="pm-group-left">
            <div class="pm-group-plus">+</div>
            <div>
                <div class="pm-group-title">{{ $group->name }}</div>
                <div class="pm-group-meta">
                    <span>{{ $group->children_count ?? $group->children->count() }} subprojetos</span>
                    <span>{{ $group->aggregated_tasks_done ?? 0 }}/{{ $group->aggregated_tasks_total ?? 0 }} tasks</span>
                    <span>{{ $group->progress_percent ?? 0 }}%</span>
                </div>
            </div>
        </div>
        <div>
            <a href="{{ route('project_manager.show', $group) }}" class="btn btn-sm btn-outline-primary"> <i class="fa-solid fa-eye"></i> </a>
        </div>
    </div>
    <div class="pm-group-body">
        <div class="pm-group-cards">
            @foreach($group->children as $project)
                @include('project-manager::Includes._components.project-card', ['project' => $project])
            @endforeach
        </div>
    </div>
</div>
