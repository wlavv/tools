<div class="pm-project-grid">
    @forelse($projects as $project)
        <div class="pm-project-card">
            <h3>{{ $project->name }}</h3>
            <div class="mb-2">{{ $project->status }}</div>
            <div class="mb-2"><small>{{ $project->description ?: 'Sem descrição.' }}</small></div>
            <div class="pm-progress mb-2">
                <div class="pm-progress-bar" style="width: {{ $project->progress_percent }}%;"></div>
            </div>
            <div class="mb-3"><small>{{ $project->progress_percent }}% concluído</small></div>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-sm btn-primary" href="{{ route('project_manager.show', $project) }}">Ver</a>
                <a class="btn btn-sm btn-warning" href="{{ route('project_manager.edit', $project) }}">Editar</a>
            </div>
        </div>
    @empty
        <div class="pm-card">Sem projetos registados.</div>
    @endforelse
</div>
