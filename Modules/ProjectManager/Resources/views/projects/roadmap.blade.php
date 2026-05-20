@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @include('project-manager::partials.project-tabs', ['activeTab' => 'roadmap'])

        <div class="pm-card">
            <div class="pm-section-bar">
                <div>
                    <div class="pm-card-title"><i class="fa-solid fa-timeline"></i> Roadmap timeline</div>
                    <div class="pm-card-subtitle mb-0">Milestones em sequência, com estado visual e tasks carregadas só quando o milestone é aberto.</div>
                </div>
                <a class="pm-btn pm-btn--success" href="{{ route('project_manager.projects.tasks.create', $project->id) }}"><i class="fa-solid fa-plus"></i> Novo milestone</a>
            </div>

            <div class="pm-timeline">
                @forelse($milestones as $milestone)
                    @php
                        $status = $milestone->status ?? 'pending';
                        $isCurrent = $activeMilestone && (int)$activeMilestone->id === (int)$milestone->id;
                        $isDone = in_array($status, ['done','completed']);
                        $progress = $milestoneProgress[(int)$milestone->id] ?? ['total' => 0, 'closed' => 0, 'open' => 0, 'percent' => 0, 'can_complete' => false];
                    @endphp
                    <details class="pm-timeline-item {{ $isCurrent ? 'is-current' : '' }} {{ $isDone ? 'is-done' : '' }}" data-pm-ajax-details data-url="{{ route('project_manager.projects.ajax.milestone_tasks', [$project->id, $milestone->id]) }}">
                        <summary>
                            <span class="pm-timeline-marker"><i class="fa-solid {{ $isDone ? 'fa-check' : ($isCurrent ? 'fa-play' : 'fa-flag') }}"></i></span>
                            <span class="pm-timeline-content">
                                <strong>{{ $milestone->title }}</strong>
                                <small>{{ $status }} · {{ $progress['closed'] }}/{{ $progress['total'] }} tasks · {{ $progress['percent'] }}%</small>
                            </span>
                            <span class="pm-pill">abrir tasks</span>
                        </summary>
                        <div class="pm-timeline-body">
                            <div class="pm-milestone-progress">
                                <div class="pm-progress-head">
                                    <strong>Progresso do milestone</strong>
                                    <span>{{ $progress['percent'] }}%</span>
                                </div>
                                <div class="pm-progress"><span style="width: {{ $progress['percent'] }}%"></span></div>
                                @if($isCurrent && ($progress['can_complete'] ?? false))
                                    <form method="POST" action="{{ route('project_manager.projects.milestones.complete', [$project->id, $milestone->id]) }}" class="mt-3">
                                        @csrf
                                        <button type="submit" class="pm-btn pm-btn--success" onclick="return confirm('Concluir este milestone e iniciar o próximo?')">
                                            <i class="fa-solid fa-circle-check"></i> Concluir milestone e avançar
                                        </button>
                                    </form>
                                @elseif($isCurrent)
                                    <div class="pm-muted pm-small mt-2">Ainda existem {{ $progress['open'] }} task(s) abertas.</div>
                                @endif
                            </div>
                            <div data-pm-ajax-target><div class="pm-loading">A carregar tasks...</div></div>
                        </div>
                    </details>
                @empty
                    <div class="pm-empty">Sem milestones para desenhar roadmap.</div>
                @endforelse
            </div>
        </div>

        <div class="pm-card mt-3">
            <div class="pm-section-bar">
                <div>
                    <div class="pm-card-title"><i class="fa-solid fa-diagram-project"></i> Encadeamento gráfico</div>
                    <div class="pm-card-subtitle mb-0">Vista compacta para perceber rapidamente o que está feito, atual e seguinte.</div>
                </div>
            </div>
            <div class="pm-roadmap-flow">
                @forelse($milestones as $milestone)
                    @php
                        $status = $milestone->status ?? 'pending';
                        $isCurrent = $activeMilestone && (int)$activeMilestone->id === (int)$milestone->id;
                    @endphp
                    <div class="pm-flow-node {{ $isCurrent ? 'is-current' : '' }} {{ in_array($status, ['done','completed']) ? 'is-done' : '' }}">
                        <i class="fa-solid fa-flag"></i>
                        <span>{{ $milestone->title }}</span>
                    </div>
                @empty
                    <div class="pm-empty">Sem dados.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@include('project-manager::partials.ajax-details-script')
@endsection
