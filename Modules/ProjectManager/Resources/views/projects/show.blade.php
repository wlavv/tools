@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')
@php use Illuminate\Support\Str; use Modules\ProjectManager\Services\ProjectManagerSectionRegistry; @endphp

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @php $projectLogoUrl = $project->logo ?? ($primaryLogo->public_url ?? null); @endphp
        <div class="pm-wc-project-hero">
            @if($projectLogoUrl)
                <img class="pm-wc-project-logo" src="{{ $projectLogoUrl }}" alt="{{ $project->name }} logo">
            @else
                <div class="pm-wc-project-logo">{{ strtoupper(substr($project->name ?? 'P', 0, 2)) }}</div>
            @endif
            <div>
                <h2 class="pm-wc-project-title">{{ $project->name }}</h2>
                <div class="pm-wc-project-meta">
                    <span class="pm-pill pm-pill--gold"><i class="fa-solid fa-circle"></i> {{ $project->status ?? 'open' }}</span>
                    @if($activeMilestone)<span class="pm-pill"><i class="fa-solid fa-flag-checkered"></i> {{ $activeMilestone->title }}</span>@endif
                    <span class="pm-pill"><i class="fa-solid fa-list-check"></i> {{ $activeMilestoneProgress['closed'] ?? 0 }}/{{ $activeMilestoneProgress['total'] ?? 0 }} tasks</span>
                </div>
            </div>
            <div class="pm-wc-project-actions">
                <a class="pm-btn pm-btn--primary" href="{{ route('project_manager.projects.details', $project->id) }}"><i class="fa-solid fa-sliders"></i> Detalhes</a>
                <a class="pm-btn pm-btn--success" href="{{ route('project_manager.projects.tasks.create', $project->id) }}"><i class="fa-solid fa-plus"></i> Task</a>
            </div>
        </div>

        @include('project-manager::partials.project-tabs', ['activeTab' => 'overview'])

        <div class="pm-two-col pm-two-col--wide-right">
            <div class="pm-card">
                <div class="pm-section-bar">
                    <div>
                        <div class="pm-card-title"><i class="fa-solid fa-play"></i> Próximas tasks acionáveis</div>
                        <div class="pm-card-subtitle mb-0">Milestone atual do projeto aberto.</div>
                    </div>
                    <a class="pm-btn pm-btn--success" href="{{ route('project_manager.projects.tasks.create', $project->id) }}"><i class="fa-solid fa-plus"></i> Nova task</a>
                </div>

                @if($activeMilestone)
                    <details class="pm-accordion" open>
                        <summary>
                            <span><i class="fa-solid fa-flag-checkered"></i> {{ $activeMilestone->title }}</span>
                            <span class="pm-pill pm-pill--gold">{{ $activeMilestone->status ?? 'open' }}</span>
                        </summary>
                        <div class="pm-accordion-body">
                            <div class="pm-milestone-progress">
                                <div class="pm-progress-head">
                                    <strong>{{ $activeMilestoneProgress['closed'] ?? 0 }}/{{ $activeMilestoneProgress['total'] ?? 0 }} tasks concluídas</strong>
                                    <span>{{ $activeMilestoneProgress['percent'] ?? 0 }}%</span>
                                </div>
                                <div class="pm-progress"><span style="width: {{ $activeMilestoneProgress['percent'] ?? 0 }}%"></span></div>

                                @if(($activeMilestoneProgress['can_complete'] ?? false))
                                    <form method="POST" action="{{ route('project_manager.projects.milestones.complete', [$project->id, $activeMilestone->id]) }}" class="mt-3">
                                        @csrf
                                        <button type="submit" class="pm-btn pm-btn--success" onclick="return confirm('Concluir este milestone e iniciar o próximo?')">
                                            <i class="fa-solid fa-circle-check"></i> Concluir milestone e avançar
                                        </button>
                                    </form>
                                @else
                                    <div class="pm-muted pm-small mt-2">Conclui todas as tasks deste milestone para desbloquear a passagem para o milestone seguinte.</div>
                                @endif
                            </div>

                            @include('project-manager::partials.milestone-tasks-tree', ['tasks' => $activeTasks])
                        </div>
                    </details>
                @else
                    <div class="pm-empty">Ainda não existe milestone aberto. Cria uma task do tipo <strong>milestone</strong>.</div>
                @endif
            </div>

            <div class="pm-card">
                <div class="pm-section-bar">
                    <div>
                        <div class="pm-card-title"><i class="fa-solid fa-route"></i> Roadmap aberto</div>
                        <div class="pm-card-subtitle mb-0">Milestones seguintes, colapsáveis para ocupar pouco espaço.</div>
                    </div>
                    <a class="pm-btn pm-btn--primary" href="{{ route('project_manager.projects.roadmap.index', $project->id) }}"><i class="fa-solid fa-diagram-project"></i> Ver roadmap</a>
                </div>

                @forelse($nextMilestones as $milestone)
                    <details class="pm-accordion" data-pm-ajax-details data-url="{{ route('project_manager.projects.ajax.milestone_tasks', [$project->id, $milestone->id]) }}">
                        <summary>
                            <span><i class="fa-solid fa-flag"></i> {{ $milestone->title }}</span>
                            <span class="pm-pill">{{ $milestone->status ?? 'pending' }}</span>
                        </summary>
                        <div class="pm-accordion-body" data-pm-ajax-target>
                            <div class="pm-loading">A carregar tasks...</div>
                        </div>
                    </details>
                @empty
                    <div class="pm-empty">Sem próximos milestones abertos.</div>
                @endforelse
            </div>
        </div>

        <div class="pm-card mt-3">
            <div class="pm-section-bar">
                <div>
                    <div class="pm-card-title"><i class="fa-solid fa-scale-balanced"></i> Decisões recentes</div>
                    <div class="pm-card-subtitle mb-0">Apenas decisões úteis para orientar execução.</div>
                </div>
                <a class="pm-btn pm-btn--success" href="{{ route(ProjectManagerSectionRegistry::routeName('decisions', 'create'), $project->id) }}"><i class="fa-solid fa-plus"></i> Nova decisão</a>
            </div>
            <table class="pm-table">
                <thead><tr><th>Decisão</th><th>Status</th><th class="text-end">Ação</th></tr></thead>
                <tbody>
                @forelse($recentDecisions as $decision)
                    <tr>
                        <td><strong>{{ $decision->title }}</strong><div class="pm-muted pm-small">{{ Str::limit($decision->decision ?? $decision->context ?? '', 120) }}</div></td>
                        <td><span class="pm-pill">{{ $decision->status ?? '-' }}</span></td>
                        <td class="text-end"><a class="pm-btn pm-btn--compact pm-btn--warning" href="{{ route(ProjectManagerSectionRegistry::routeName('decisions', 'edit'), [$project->id, $decision->id]) }}"><i class="fa-solid fa-pencil"></i> Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="pm-empty">Sem decisões registadas.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@include('project-manager::partials.ajax-details-script')
@endsection
