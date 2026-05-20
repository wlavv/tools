@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @include('project-manager::partials.project-tabs', ['activeTab' => 'tasks'])

        <div class="pm-card">
            <div class="pm-section-bar">
                <div>
                    <div class="pm-card-title"><i class="fa-solid fa-list-check"></i> Tasks por milestone</div>
                    <div class="pm-card-subtitle mb-0">Milestones concluídos e por concluir. O conteúdo abre por AJAX ao clicar.</div>
                </div>
                <a class="pm-btn pm-btn--success" href="{{ route('project_manager.projects.tasks.create', $project->id) }}"><i class="fa-solid fa-plus"></i> Nova task / milestone</a>
            </div>

            @forelse($milestones as $milestone)
                <details class="pm-accordion" data-pm-ajax-details data-url="{{ route('project_manager.projects.ajax.milestone_tasks', [$project->id, $milestone->id]) }}">
                    <summary>
                        <span><i class="fa-solid fa-flag-checkered"></i> {{ $milestone->title }}</span>
                        <span class="pm-accordion-meta">
                            <span class="pm-pill {{ in_array(($milestone->status ?? ''), ['done','completed']) ? 'pm-pill--success' : (($milestone->status ?? '') === 'blocked' ? 'pm-pill--danger' : '') }}">{{ $milestone->status ?? 'pending' }}</span>
                            <a class="pm-btn pm-btn--compact pm-btn--warning" href="{{ route('project_manager.projects.tasks.edit', [$project->id, $milestone->id]) }}" onclick="event.stopPropagation();"><i class="fa-solid fa-pencil"></i></a>
                        </span>
                    </summary>
                    <div class="pm-accordion-body" data-pm-ajax-target><div class="pm-loading">A carregar tasks...</div></div>
                </details>
            @empty
                <div class="pm-empty">Ainda não existem milestones. Cria uma task com tipo <strong>milestone</strong>.</div>
            @endforelse
        </div>
    </div>
</div>
@include('project-manager::partials.ajax-details-script')
@endsection
