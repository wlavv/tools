@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')
@php
    $projectGroups = $projectGroups ?? ['execution' => collect(), 'hold' => collect(), 'pending' => collect(), 'done' => collect()];
    $stats = $stats ?? [];
    $moduleGovernance = $moduleGovernance ?? [
        'available' => false,
        'has_scan' => false,
        'counters' => ['modules' => 0, 'broken' => 0, 'incomplete' => 0, 'saas_candidates' => 0, 'dependency_impacts' => 0],
        'critical_modules' => collect(),
    ];

    $closureBlockers = ($moduleGovernance['counters']['broken'] ?? 0) + ($moduleGovernance['counters']['incomplete'] ?? 0);
    $executionProjects = $projectGroups['execution'] ?? collect();
    $holdProjects = $projectGroups['hold'] ?? collect();
    $pendingProjects = $projectGroups['pending'] ?? collect();

    $dashboardCards = [
        [
            'label' => 'Operations',
            'description' => 'Execucao global, Eisenhower, Gantt, governance e quick task.',
            'icon' => 'fa-solid fa-table-columns',
            'route' => 'project_manager.operations',
            'metric' => ($stats['matrix_tasks'] ?? 0) . ' tasks',
        ],
        [
            'label' => 'Productivity',
            'description' => 'Fluxo operacional por estado, bloqueios e dependencias globais.',
            'icon' => 'fa-solid fa-gauge-high',
            'route' => 'project_manager.productivity',
            'metric' => ($stats['blocked'] ?? 0) . ' blocked',
        ],
        [
            'label' => 'Projects',
            'description' => 'Lista completa de projetos, filtros e entrada em cada workspace.',
            'icon' => 'fa-solid fa-folder-tree',
            'route' => 'project_manager.projects.index',
            'metric' => ($stats['projects_execution'] ?? 0) . ' active',
        ],
    ];
@endphp

<style>
    .pm-hub-kpis{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .pm-hub-kpi{position:relative;overflow:hidden;min-height:94px;padding:14px;border:1px solid var(--pm-border);border-radius:var(--pm-radius);background:var(--pm-surface);box-shadow:var(--pm-shadow);display:flex;align-items:center;justify-content:space-between;gap:12px}
    .pm-hub-kpi span{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--pm-muted);font-weight:900}
    .pm-hub-kpi strong{display:block;margin-top:6px;font-size:28px;line-height:1;color:var(--pm-text);font-weight:900}
    .pm-hub-kpi-icon{width:42px;height:42px;border-radius:var(--pm-radius);display:inline-flex;align-items:center;justify-content:center;flex:0 0 42px;background:rgba(var(--pm-accent-rgb),.12);border:1px solid rgba(var(--pm-accent-rgb),.26);color:var(--pm-accent)}
    .pm-hub-kpi--success{--pm-kpi-color:34,197,94}.pm-hub-kpi--info{--pm-kpi-color:37,99,235}.pm-hub-kpi--warning{--pm-kpi-color:245,158,11}.pm-hub-kpi--danger{--pm-kpi-color:220,38,38}
    .pm-hub-kpi[class*="pm-hub-kpi--"] .pm-hub-kpi-icon{background:rgba(var(--pm-kpi-color),.12);border-color:rgba(var(--pm-kpi-color),.26);color:rgb(var(--pm-kpi-color))}
    .pm-hub-overview{display:grid;grid-template-columns:minmax(0,8fr) minmax(320px,4fr);gap:12px;margin-bottom:12px;align-items:stretch}
    .pm-hub-nav-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    .pm-hub-card{display:flex;flex-direction:column;gap:12px;min-height:176px;text-decoration:none}
    .pm-hub-card:hover{border-color:var(--pm-accent)!important;text-decoration:none}
    .pm-hub-card-icon{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(var(--pm-accent-rgb),.35);border-radius:var(--pm-radius);background:rgba(var(--pm-accent-rgb),.1);color:var(--pm-accent)}
    .pm-hub-card strong{font-size:1.05rem;color:var(--pm-text)}
    .pm-hub-card p{margin:0;color:var(--pm-muted);font-size:.88rem;line-height:1.4}
    .pm-hub-card-foot{margin-top:auto;display:flex;align-items:center;justify-content:space-between;color:var(--pm-muted);font-size:.8rem;font-weight:800}
    .pm-hub-project-list{display:grid;gap:8px}
    .pm-hub-project-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center;padding:10px;border:1px solid var(--pm-border);border-radius:var(--pm-radius);background:var(--pm-surface)}
    .pm-hub-project-row strong{display:block;color:var(--pm-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .pm-hub-project-row span{display:block;color:var(--pm-muted);font-size:.78rem;margin-top:2px}
    .pm-hub-side-stack{display:grid;gap:12px}
    .pm-hub-governance{height:100%}
    .pm-hub-governance .pm-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .pm-hub-pipeline .pm-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .pm-hub-governance .pm-hub-kpi{min-height:78px;padding:11px}
    .pm-hub-governance .pm-hub-kpi strong{font-size:22px}
    .pm-hub-governance .pm-hub-kpi-icon{width:36px;height:36px;flex-basis:36px}
    @media(max-width:1100px){.pm-hub-overview{grid-template-columns:1fr}.pm-hub-nav-grid{grid-template-columns:1fr}}
    @media(max-width:560px){.pm-hub-governance .pm-grid,.pm-hub-pipeline .pm-grid{grid-template-columns:1fr}}
    @media(max-width:680px){.pm-hub-kpis{grid-template-columns:1fr}}
</style>

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @if(session('success'))
            <div class="pm-alert">{{ session('success') }}</div>
        @endif

        <div class="pm-hub-overview">
            <section>
                <div class="pm-hub-nav-grid">
                    @foreach($dashboardCards as $card)
                        <a class="pm-card pm-hub-card" href="{{ route($card['route']) }}">
                            <span class="pm-hub-card-icon"><i class="{{ $card['icon'] }}"></i></span>
                            <div>
                                <strong>{{ $card['label'] }}</strong>
                                <p>{{ $card['description'] }}</p>
                            </div>
                            <span class="pm-hub-card-foot">
                                <span>{{ $card['metric'] }}</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>

                <div class="pm-card mt-3">
                    <div class="pm-section-bar">
                        <div>
                            <div class="pm-card-title"><i class="fa-solid fa-play"></i> Projetos em execucao</div>
                            <div class="pm-card-subtitle mb-0">Acesso rapido aos workspaces ativos.</div>
                        </div>
                        <a class="pm-btn pm-btn--compact" href="{{ route('project_manager.projects.index') }}">Ver todos</a>
                    </div>

                    <div class="pm-hub-project-list">
                        @forelse($executionProjects->take(10) as $project)
                            <div class="pm-hub-project-row">
                                <div>
                                    <strong>{{ $project->name }}</strong>
                                    <span>{{ $project->status ?? 'active' }}</span>
                                </div>
                                <a class="pm-btn pm-btn--compact" href="{{ route('project_manager.projects.show', $project->id) }}"><i class="fa-solid fa-arrow-right"></i></a>
                            </div>
                        @empty
                            <div class="pm-empty">Sem projetos em execucao.</div>
                        @endforelse
                    </div>
                </div>
            </section>

            <aside class="pm-hub-side-stack">
                <section class="pm-card">
                    <div class="pm-hub-kpis">
                        <div class="pm-hub-kpi pm-hub-kpi--success">
                            <div><span>Em execucao</span><strong>{{ $stats['projects_execution'] ?? 0 }}</strong></div>
                            <div class="pm-hub-kpi-icon"><i class="fa-solid fa-play"></i></div>
                        </div>
                        <div class="pm-hub-kpi pm-hub-kpi--info">
                            <div><span>Milestones ativos</span><strong>{{ $stats['active_milestones'] ?? 0 }}</strong></div>
                            <div class="pm-hub-kpi-icon"><i class="fa-solid fa-flag-checkered"></i></div>
                        </div>
                        <div class="pm-hub-kpi pm-hub-kpi--warning">
                            <div><span>Tasks abertas</span><strong>{{ $stats['matrix_tasks'] ?? 0 }}</strong></div>
                            <div class="pm-hub-kpi-icon"><i class="fa-solid fa-list-check"></i></div>
                        </div>
                        <div class="pm-hub-kpi pm-hub-kpi--danger">
                            <div><span>Bloqueios</span><strong>{{ $stats['blocked'] ?? 0 }}</strong></div>
                            <div class="pm-hub-kpi-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        </div>
                    </div>
                </section>

                <section class="pm-card pm-hub-governance">
                    <div class="pm-card-title"><i class="fa-solid fa-shield-halved"></i> Governance</div>
                    @if(!($moduleGovernance['available'] ?? false))
                        <div class="pm-empty pm-empty--small">ModuleHealth ainda nao esta disponivel.</div>
                    @elseif(!($moduleGovernance['has_scan'] ?? false))
                        <div class="pm-empty pm-empty--small">Sem scan estrutural ainda.</div>
                    @else
                        <div class="pm-grid">
                            <div class="pm-hub-kpi pm-hub-kpi--info"><div><span>Modules</span><strong>{{ $moduleGovernance['counters']['modules'] ?? 0 }}</strong></div><div class="pm-hub-kpi-icon"><i class="fa-solid fa-cubes"></i></div></div>
                            <div class="pm-hub-kpi pm-hub-kpi--danger"><div><span>Closure blockers</span><strong>{{ $closureBlockers }}</strong></div><div class="pm-hub-kpi-icon"><i class="fa-solid fa-shield-halved"></i></div></div>
                            <div class="pm-hub-kpi pm-hub-kpi--success"><div><span>SaaS candidates</span><strong>{{ $moduleGovernance['counters']['saas_candidates'] ?? 0 }}</strong></div><div class="pm-hub-kpi-icon"><i class="fa-solid fa-cloud"></i></div></div>
                            <div class="pm-hub-kpi pm-hub-kpi--warning"><div><span>Dependency impacts</span><strong>{{ $moduleGovernance['counters']['dependency_impacts'] ?? 0 }}</strong></div><div class="pm-hub-kpi-icon"><i class="fa-solid fa-code-branch"></i></div></div>
                        </div>
                    @endif
                </section>

                <section class="pm-card pm-hub-pipeline">
                    <div class="pm-card-title"><i class="fa-solid fa-layer-group"></i> Pipeline</div>
                    <div class="pm-grid">
                        <div class="pm-hub-kpi pm-hub-kpi--warning"><div><span>Hold</span><strong>{{ $holdProjects->count() }}</strong></div><div class="pm-hub-kpi-icon"><i class="fa-solid fa-pause"></i></div></div>
                        <div class="pm-hub-kpi pm-hub-kpi--info"><div><span>Pending</span><strong>{{ $pendingProjects->count() }}</strong></div><div class="pm-hub-kpi-icon"><i class="fa-solid fa-clock"></i></div></div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</div>
@endsection
