@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')
@php
    use Illuminate\Support\Str;

    $projects = $projects ?? collect();
    $projectGroups = $projectGroups ?? ['execution' => collect(), 'hold' => collect(), 'pending' => collect(), 'done' => collect()];
    $activeMilestones = $activeMilestones ?? [];
    $milestoneCards = $milestoneCards ?? collect($activeMilestones)->values();
    $matrixTasks = $matrixTasks ?? collect();
    $ganttTasks = $ganttTasks ?? collect();
    $executionCounters = $executionCounters ?? [];
    $stats = $stats ?? [];
    $quickProjects = $quickProjects ?? collect();
    $quickMilestones = $quickMilestones ?? collect();
    $quickParentTasks = $quickParentTasks ?? collect();
    $selectedProjectId = $selectedProjectId ?? null;

    $matrixGroups = [
        'do_now' => ['label' => 'Fazer agora', 'hint' => 'Importante + urgente', 'importance' => 5, 'urgency' => 5, 'items' => collect(), 'icon' => 'fa-fire'],
        'plan' => ['label' => 'Planear', 'hint' => 'Importante + pouco urgente', 'importance' => 5, 'urgency' => 2, 'items' => collect(), 'icon' => 'fa-calendar-check'],
        'delegate' => ['label' => 'Delegar / simplificar', 'hint' => 'Urgente + menos importante', 'importance' => 2, 'urgency' => 5, 'items' => collect(), 'icon' => 'fa-bolt'],
        'later' => ['label' => 'Mais tarde', 'hint' => 'Pouco urgente + menos importante', 'importance' => 2, 'urgency' => 2, 'items' => collect(), 'icon' => 'fa-box-archive'],
    ];

    foreach ($matrixTasks as $task) {
        $importance = (int)($task->importance ?? max(1, 6 - (int)($task->priority ?? 3)));
        $urgency = (int)($task->urgency ?? (in_array(($task->status ?? ''), ['in_progress', 'review']) ? 5 : 2));
        if ($importance >= 4 && $urgency >= 4) { $matrixGroups['do_now']['items']->push($task); }
        elseif ($importance >= 4) { $matrixGroups['plan']['items']->push($task); }
        elseif ($urgency >= 4) { $matrixGroups['delegate']['items']->push($task); }
        else { $matrixGroups['later']['items']->push($task); }
    }

    $statusLabels = [
        'in_progress' => 'Em execução',
        'review' => 'Review',
        'ready' => 'Ready',
        'pending' => 'Pending',
        'waiting' => 'Waiting',
        'blocked' => 'Blocked',
        'done' => 'Done',
        'completed' => 'Done',
    ];

    $totalVisible = max(1, $matrixTasks->count());
@endphp

<style>
    .pm-entry-layout{display:grid;grid-template-columns:320px minmax(0,1fr);gap:16px;align-items:start;}
    .pm-project-sidebar{position:sticky;top:12px;max-height:calc(100vh - 140px);overflow:auto;}
    .pm-project-group{margin-bottom:14px;}
    .pm-project-group-title{display:flex;align-items:center;justify-content:space-between;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--pm-muted);margin:0 0 8px;}
    .pm-project-accordion{border:1px solid var(--pm-border);border-radius:10px;background:rgba(255,255,255,.7);overflow:hidden;margin-top:10px;}
    .pm-project-accordion summary{list-style:none;cursor:pointer;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 11px;background:linear-gradient(135deg,#fff,#f8f7f1);font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;color:#374151;}
    .pm-project-accordion summary::-webkit-details-marker{display:none;}
    .pm-project-accordion summary .pm-chevron{transition:.15s ease;color:#9ca3af;}
    .pm-project-accordion[open] summary .pm-chevron{transform:rotate(90deg);color:#8a6d18;}
    .pm-project-accordion-body{padding:9px;border-top:1px solid var(--pm-border);}
    .pm-project-list{display:flex;flex-direction:column;gap:7px;}
    .pm-project-sidebar-head{align-items:flex-start;}

    .pm-project-filter-main{display:flex;align-items:center;gap:9px;min-width:0;}
    .pm-project-logo{width:34px;height:34px;border-radius:8px;object-fit:cover;border:1px solid rgba(201,166,70,.35);background:#fff;box-shadow:0 4px 10px rgba(17,24,39,.08);flex:0 0 34px;}
    .pm-project-logo-fallback{width:34px;height:34px;border-radius:8px;border:1px solid rgba(201,166,70,.45);background:linear-gradient(135deg,#fff8dc,#f5e3a0);color:#8a6d18;font-size:12px;font-weight:900;display:inline-flex;align-items:center;justify-content:center;box-shadow:0 4px 10px rgba(17,24,39,.08);flex:0 0 34px;}
    .pm-project-name-wrap{min-width:0;}
    .pm-project-name-wrap strong,.pm-project-name-wrap small{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .pm-icon-action{width:34px;height:34px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;border:1px solid rgba(201,166,70,.7);background:linear-gradient(135deg,#fff,#f8f3df);color:#8a6d18;text-decoration:none;flex:0 0 34px;}
    .pm-project-row{display:grid;grid-template-columns:minmax(0,1fr) 34px;gap:6px;align-items:stretch;}
    .pm-quick-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .pm-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:1050;display:none;align-items:center;justify-content:center;padding:18px;}
    .pm-modal-backdrop.is-open{display:flex;}
    .pm-modal-panel{width:min(760px,100%);max-height:calc(100vh - 36px);overflow:auto;background:linear-gradient(135deg,#fff,#faf8ef);border:1px solid rgba(201,166,70,.35);border-radius:14px;box-shadow:0 24px 70px rgba(15,23,42,.22);}
    .pm-modal-head{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;padding:16px 18px;border-bottom:1px solid var(--pm-border);}
    .pm-modal-body{padding:18px;}
    .pm-modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
    .pm-form-field label{display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:var(--pm-muted);margin-bottom:5px;}
    .pm-form-field input,.pm-form-field select,.pm-form-field textarea{width:100%;border:1px solid var(--pm-border);border-radius:8px;padding:8px 10px;background:#fff;color:var(--pm-text);}
    .pm-form-field textarea{min-height:90px;resize:vertical;}
    .pm-form-field--full{grid-column:1 / -1;}
    .pm-modal-foot{display:flex;justify-content:flex-end;gap:8px;padding:14px 18px;border-top:1px solid var(--pm-border);}
    .pm-btn--success{background:linear-gradient(135deg,#16a34a,#22c55e);border-color:#16a34a;color:#fff;}
    @media(max-width:760px){.pm-modal-grid{grid-template-columns:1fr}}
    .pm-open-project{border:1px solid var(--pm-border);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--pm-muted);background:rgba(255,255,255,.8);text-decoration:none;}
    .pm-open-project:hover{border-color:rgba(201,166,70,.9);color:#8a6d18;background:#fff8dc;}
    .pm-project-filter{width:100%;border:1px solid var(--pm-border);background:linear-gradient(135deg,rgba(255,255,255,.94),rgba(248,247,242,.86));border-radius:8px;padding:9px 10px;text-align:left;display:flex;align-items:center;justify-content:space-between;gap:8px;color:var(--pm-text);transition:.15s ease;}
    .pm-project-filter:hover,.pm-project-filter.is-active{border-color:rgba(201,166,70,.9);box-shadow:0 8px 18px rgba(17,24,39,.08);transform:translateY(-1px);}
    .pm-project-filter strong{display:block;font-size:13px;line-height:1.2;}
    .pm-project-filter small{display:block;color:var(--pm-muted);font-size:11px;margin-top:2px;}
    .pm-status-dot{width:9px;height:9px;border-radius:99px;background:#d1d5db;flex:0 0 9px;}
    .pm-status-dot--execution{background:#22c55e}.pm-status-dot--hold{background:#f59e0b}.pm-status-dot--pending{background:#94a3b8}.pm-status-dot--done{background:#64748b}
    .pm-entry-top{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px;}
    .pm-mini-stat{border:1px solid var(--pm-border);border-radius:10px;padding:12px;background:linear-gradient(135deg,rgba(255,255,255,.95),rgba(248,247,242,.9));}
    .pm-mini-stat span{display:block;color:var(--pm-muted);font-size:11px;text-transform:uppercase;font-weight:800;letter-spacing:.08em}.pm-mini-stat strong{font-size:22px;line-height:1.1;}
    .pm-selected-bar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;}
    .pm-charts-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;}
    .pm-progress-item{margin-bottom:12px;}.pm-progress-head{display:flex;justify-content:space-between;gap:10px;font-size:12px;margin-bottom:5px;}.pm-progress-track{height:9px;border-radius:99px;background:rgba(148,163,184,.18);overflow:hidden}.pm-progress-track span{display:block;height:100%;border-radius:inherit;background:linear-gradient(90deg,#c9a646,#f2d57e);}
    .pm-gantt--wide .pm-gantt-label{width:260px;}.pm-gantt-task-meta{font-size:11px;color:var(--pm-muted);margin-top:2px;}
    .pm-hidden-by-filter{display:none!important;}
    @media(max-width:1100px){.pm-entry-layout{grid-template-columns:1fr}.pm-project-sidebar{position:relative;top:auto;max-height:none}.pm-entry-top,.pm-charts-row{grid-template-columns:1fr 1fr}}
    @media(max-width:680px){.pm-entry-top,.pm-charts-row{grid-template-columns:1fr}}
</style>

<div class="container-fluid pm-wrap">
    <div class="pm-shell">
        @if(session('success'))
            <div class="pm-alert">{{ session('success') }}</div>
        @endif

        <div class="pm-wc-hero">
            <div class="pm-wc-hero-main">
                <div class="pm-wc-kicker">Project Control Center</div>
                <h2 class="pm-wc-title">Execução, prioridades e roadmap num só cockpit.</h2>
                <p class="pm-wc-lead">Seleciona um projeto à esquerda para focar o Eisenhower e o Gantt, ou mantém a visão global dos projetos em aberto no milestone atual.</p>
                <div class="pm-wc-hero-actions">
                    <a class="pm-btn" href="{{ route('project_manager.projects.create') }}"><i class="fa-solid fa-plus"></i> Novo projeto</a>
                    <a class="pm-btn" href="{{ route('project_manager.productivity') }}"><i class="fa-solid fa-bolt"></i> Productivity global</a>
                </div>
            </div>
            <div class="pm-wc-side-card">
                <div class="pm-card-title"><i class="fa-solid fa-chart-simple"></i> Indicadores rápidos</div>
                <div class="pm-card-subtitle">Resumo visual do pipeline atual.</div>
                <div class="pm-wc-metric-grid">
                    <div class="pm-wc-metric"><span>Projetos</span><strong>{{ $projects->count() ?? 0 }}</strong></div>
                    <div class="pm-wc-metric"><span>Tasks visíveis</span><strong>{{ $matrixTasks->count() ?? 0 }}</strong></div>
                    <div class="pm-wc-metric"><span>Milestones</span><strong>{{ $milestoneCards->count() ?? 0 }}</strong></div>
                    <div class="pm-wc-metric"><span>Foco</span><strong>{{ $selectedProjectId ? '1' : 'Global' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="pm-entry-layout">
            <aside class="pm-card pm-project-sidebar">
                <div class="pm-section-bar pm-project-sidebar-head">
                    <div>
                        <div class="pm-card-title"><i class="fa-solid fa-folder-tree"></i> Projetos</div>
                        <div class="pm-card-subtitle mb-0">Seleciona um projeto para filtrar execução.</div>
                    </div>
                    <a class="pm-icon-action" href="{{ route('project_manager.projects.create') }}" title="Novo projeto"><i class="fa-solid fa-plus"></i></a>
                </div>

                <button type="button" class="pm-project-filter is-active" data-project-filter="all" data-project-label="Todos os projetos em aberto">
                    <span><strong>Todos os projetos em aberto</strong><small>Eisenhower + Gantt globais</small></span>
                    <i class="fa-solid fa-layer-group"></i>
                </button>

                @foreach([
                    'execution' => ['label' => 'Em execução', 'icon' => 'fa-play', 'open' => true],
                    'hold' => ['label' => 'Hold', 'icon' => 'fa-pause', 'open' => false],
                    'pending' => ['label' => 'Pending', 'icon' => 'fa-clock', 'open' => false],
                    'done' => ['label' => 'Done', 'icon' => 'fa-check', 'open' => false],
                ] as $groupKey => $groupMeta)
                    <details class="pm-project-accordion" {{ $groupMeta['open'] ? 'open' : '' }}>
                        <summary>
                            <span><i class="fa-solid {{ $groupMeta['icon'] }}"></i> {{ $groupMeta['label'] }}</span>
                            <span>{{ ($projectGroups[$groupKey] ?? collect())->count() }} <i class="fa-solid fa-chevron-right pm-chevron"></i></span>
                        </summary>
                        <div class="pm-project-accordion-body">
                            <div class="pm-project-list">
                                @forelse(($projectGroups[$groupKey] ?? collect()) as $project)
                                    @php
                                        $milestone = $activeMilestones[(int) $project->id] ?? null;
                                    @endphp
                                    <div class="pm-project-row" data-project-row="{{ $project->id }}">
                                        <button type="button" class="pm-project-filter" data-project-filter="{{ $project->id }}" data-project-label="{{ $project->name }}">
                                            <span class="pm-project-filter-main">
                                                @if(!empty($project->project_logo_url))
                                                    <img class="pm-project-logo" src="{{ $project->project_logo_url }}" alt="{{ $project->name }}">
                                                @else
                                                    <span class="pm-project-logo-fallback">{{ $project->project_initials ?? 'P' }}</span>
                                                @endif
                                                <span class="pm-project-name-wrap">
                                                    <strong>{{ $project->name }}</strong>
                                                    <small>{{ $milestone->title ?? ($project->status ?? 'sem milestone ativo') }}</small>
                                                </span>
                                            </span>
                                            <span class="pm-status-dot pm-status-dot--{{ $groupKey }}"></span>
                                        </button>
                                        <a class="pm-open-project" href="{{ route('project_manager.projects.show', $project->id) }}" title="Entrar no projeto"><i class="fa-solid fa-arrow-right"></i></a>
                                    </div>
                                @empty
                                    <div class="pm-empty pm-empty--small">Sem projetos.</div>
                                @endforelse
                            </div>
                        </div>
                    </details>
                @endforeach

            </aside>

            <main class="pm-main-zone">
                <div class="pm-entry-top">
                    <div class="pm-mini-stat"><span>Projetos em execução</span><strong data-stat-execution-projects>{{ $stats['projects_execution'] ?? 0 }}</strong></div>
                    <div class="pm-mini-stat"><span>Milestones ativos</span><strong data-stat-active-milestones>{{ $stats['active_milestones'] ?? 0 }}</strong></div>
                    <div class="pm-mini-stat"><span>Tasks priorizáveis</span><strong data-total-matrix>{{ $stats['matrix_tasks'] ?? 0 }}</strong></div>
                    <div class="pm-mini-stat"><span>Bloqueadas</span><strong>{{ $stats['blocked'] ?? 0 }}</strong></div>
                </div>

                <div class="pm-selected-bar">
                    <div>
                        <div class="pm-card-title mb-0"><i class="fa-solid fa-gauge-high"></i> Centro operacional</div>
                        <div class="pm-card-subtitle mb-0" data-selected-label>Todos os projetos em aberto · milestone atual</div>
                    </div>
                    <a class="pm-btn pm-btn--ghost" data-selected-open href="#" style="display:none"><i class="fa-solid fa-arrow-right"></i> Entrar no projeto</a>
                </div>

                <div class="pm-card mb-3">
                    <div class="pm-section-bar">
                        <div>
                            <div class="pm-card-title"><i class="fa-solid fa-table-cells-large"></i> Eisenhower global</div>
                            <div class="pm-card-subtitle mb-0">Carrega tasks dos projetos em execução, no milestone atual. Ao selecionar um projeto, filtra a matriz.</div>
                        </div>
                        <div class="pm-quick-actions">
                            <button type="button" class="pm-btn pm-btn--success" data-open-quick-task><i class="fa-solid fa-plus"></i> Criar task rápida</button>
                            <span class="pm-save-indicator" data-pm-matrix-status>Pronto</span>
                        </div>
                    </div>

                    <div class="pm-eisenhower pm-eisenhower--dropzones">
                        @foreach($matrixGroups as $key => $group)
                            <div class="pm-eisenhower-cell" data-pm-matrix-zone="{{ $key }}" data-importance="{{ $group['importance'] }}" data-urgency="{{ $group['urgency'] }}">
                                <div class="pm-eisenhower-title"><i class="fa-solid {{ $group['icon'] }}"></i> {{ $group['label'] }}</div>
                                <div class="pm-muted pm-small mb-2">{{ $group['hint'] }}</div>
                                <div class="pm-matrix-list" data-pm-matrix-list>
                                    @forelse($group['items'] as $task)
                                        <article class="pm-matrix-task" draggable="true" data-task-id="{{ $task->id }}" data-project-id="{{ $task->project_id }}" data-update-url="{{ route('project_manager.tasks.priority_matrix', $task->id) }}" data-status-url="{{ route('project_manager.tasks.panel', $task->id) }}">
                                            <strong>{{ Str::limit($task->title, 48) }}</strong>
                                            <div class="pm-matrix-task-status">{{ $task->project_name }} · {{ $task->milestone_title }}</div>
                                            <div class="pm-matrix-task-meta">
                                                <span data-pm-importance><i class="fa-solid fa-star"></i> {{ $task->importance ?? 3 }}</span>
                                                <span data-pm-urgency><i class="fa-solid fa-bolt"></i> {{ $task->urgency ?? 3 }}</span>
                                            </div>
                                            <div class="pm-matrix-task-actions">
                                                <select class="pm-task-state-select" data-pm-task-status title="Alterar estado da task">
                                                    @php $taskStatus = str_replace(' ', '_', (string)($task->status ?? 'ready')); @endphp
                                                    <option value="next" @selected(in_array($taskStatus, ['ready','pending','waiting','open','todo','new']))>Seguinte</option>
                                                    <option value="execution" @selected($taskStatus === 'in_progress')>Em execução</option>
                                                    <option value="review" @selected($taskStatus === 'review')>Review</option>
                                                    <option value="done" @selected(in_array($taskStatus, ['done','completed']))>Done</option>
                                                </select>
                                            </div>
                                        </article>
                                    @empty
                                        <div class="pm-empty pm-empty--small" data-pm-empty>Sem tasks.</div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pm-card">
                    <div class="pm-section-bar">
                        <div>
                            <div class="pm-card-title"><i class="fa-solid fa-chart-gantt"></i> Gantt operacional</div>
                            <div class="pm-card-subtitle mb-0">Mesmo filtro da matriz: todos os projetos abertos ou apenas o projeto selecionado.</div>
                        </div>
                    </div>
                    <div class="pm-gantt pm-gantt--wide">
                        @forelse($ganttTasks as $task)
                            @php
                                $offset = max(0, min(80, ((int)($task->priority ?? 3) - 1) * 10));
                                $width = max(18, min(90, (int)($task->expected_time ?? 8) * 3));
                            @endphp
                            <div class="pm-gantt-row" data-project-id="{{ $task->project_id }}">
                                <div class="pm-gantt-label">
                                    {{ Str::limit($task->title, 38) }}
                                    <div class="pm-gantt-task-meta">{{ $task->project_name }} · {{ $task->milestone_title }}</div>
                                </div>
                                <div class="pm-gantt-track"><span style="left: {{ $offset }}%; width: {{ $width }}%;"></span></div>
                            </div>
                        @empty
                            <div class="pm-empty">Sem tasks suficientes para Gantt.</div>
                        @endforelse
                    </div>
                </div>

                <div class="pm-charts-row">
                    <div class="pm-card">
                        <div class="pm-card-title"><i class="fa-solid fa-chart-simple"></i> Contadores de execução</div>
                        <div class="pm-card-subtitle">Distribuição das tasks carregadas por estado.</div>
                        @forelse(($executionCounters['by_status'] ?? []) as $status => $count)
                            @php
                                $percent = round(($count / $totalVisible) * 100);
                            @endphp
                            <div class="pm-progress-item">
                                <div class="pm-progress-head"><span>{{ $statusLabels[$status] ?? ucfirst($status) }}</span><strong>{{ $count }}</strong></div>
                                <div class="pm-progress-track"><span style="width: {{ $percent }}%"></span></div>
                            </div>
                        @empty
                            <div class="pm-empty">Sem dados de execução.</div>
                        @endforelse
                    </div>

                    <div class="pm-card">
                        <div class="pm-card-title"><i class="fa-solid fa-bars-progress"></i> Evolução por projeto</div>
                        <div class="pm-card-subtitle">Leitura rápida das tasks do milestone atual.</div>
                        @forelse(($executionCounters['by_project'] ?? []) as $projectCounter)
                            @php
                                $percent = $projectCounter['total'] ? round((($projectCounter['done'] + $projectCounter['in_progress']) / $projectCounter['total']) * 100) : 0;
                            @endphp
                            <div class="pm-progress-item" data-project-id="{{ $projectCounter['project_id'] }}">
                                <div class="pm-progress-head"><span>{{ $projectCounter['project_name'] }}</span><strong>{{ $percent }}%</strong></div>
                                <div class="pm-progress-track"><span style="width: {{ $percent }}%"></span></div>
                                <div class="pm-muted pm-small">{{ $projectCounter['in_progress'] }} em execução · {{ $projectCounter['blocked'] }} bloqueadas · {{ $projectCounter['total'] }} total</div>
                            </div>
                        @empty
                            <div class="pm-empty">Sem projetos em execução com tasks no milestone atual.</div>
                        @endforelse
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>


<div class="pm-modal-backdrop" data-quick-task-modal aria-hidden="true">
    <div class="pm-modal-panel" role="dialog" aria-modal="true" aria-labelledby="quickTaskTitle">
        <form method="POST" action="{{ route('project_manager.quick_tasks.store') }}" data-quick-task-form>
            @csrf
            <div class="pm-modal-head">
                <div>
                    <div class="pm-card-title mb-0" id="quickTaskTitle"><i class="fa-solid fa-plus"></i> Criar task rápida</div>
                    <div class="pm-card-subtitle mb-0">Liga a task ao projeto, milestone atual e, se necessário, a uma task pai.</div>
                </div>
                <button type="button" class="pm-icon-action" data-close-quick-task aria-label="Fechar"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="pm-modal-body">
                <div class="pm-modal-grid">
                    <div class="pm-form-field">
                        <label>Projeto</label>
                        <select name="project_id" data-quick-project required>
                            <option value="">Selecionar projeto</option>
                            @foreach(($quickProjects ?? collect()) as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pm-form-field">
                        <label>Milestone</label>
                        <select name="milestone_id" data-quick-milestone required>
                            <option value="">Seleciona primeiro o projeto</option>
                        </select>
                    </div>
                    <div class="pm-form-field pm-form-field--full">
                        <label>Task pai / Subtask de</label>
                        <select name="parent_task_id" data-quick-parent-task>
                            <option value="">Criar como task direta do milestone</option>
                        </select>
                    </div>
                    <div class="pm-form-field pm-form-field--full">
                        <label>Título da task</label>
                        <input type="text" name="title" maxlength="180" required placeholder="Ex: Criar formulário inline para Project Details">
                    </div>
                    <div class="pm-form-field pm-form-field--full">
                        <label>Descrição / Notas</label>
                        <textarea name="description" placeholder="Contexto, objetivo ou critérios de conclusão."></textarea>
                    </div>
                    <div class="pm-form-field">
                        <label>Estado inicial</label>
                        <select name="status">
                            <option value="ready">Ready</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">Em execução</option>
                            <option value="waiting">Waiting</option>
                        </select>
                    </div>
                    <div class="pm-form-field">
                        <label>Prioridade</label>
                        <select name="priority">
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}" {{ $i === 5 ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="pm-form-field">
                        <label>Importância</label>
                        <select name="importance">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $i === 3 ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="pm-form-field">
                        <label>Urgência</label>
                        <select name="urgency">
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ $i === 3 ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="pm-form-field">
                        <label>Tempo previsto</label>
                        <input type="number" name="expected_time" min="0" placeholder="Horas ou unidade interna">
                    </div>
                    <div class="pm-form-field">
                        <label>Due date</label>
                        <input type="date" name="due_date">
                    </div>
                </div>
            </div>
            <div class="pm-modal-foot">
                <button type="button" class="pm-btn pm-btn--ghost" data-close-quick-task>Cancelar</button>
                <button type="submit" class="pm-btn pm-btn--success"><i class="fa-solid fa-floppy-disk"></i> Criar task</button>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    const csrf = '{{ csrf_token() }}';
    const status = document.querySelector('[data-pm-matrix-status]');
    let dragged = null;
    let activeProject = 'all';
    const projectShowBase = '{{ url(config('project-manager.route_prefix', 'project-manager') . '/projects') }}';
    const quickMilestones = @json(($quickMilestones ?? collect())->toArray());
    const quickParentTasks = @json(($quickParentTasks ?? collect())->toArray());

    function setStatus(text, mode) {
        if (!status) return;
        status.textContent = text;
        status.dataset.mode = mode || 'idle';
    }

    function refreshEmptyState(zone) {
        const list = zone.querySelector('[data-pm-matrix-list]');
        if (!list) return;
        const empty = list.querySelector('[data-pm-empty]');
        const visibleCards = Array.from(list.querySelectorAll('[data-task-id]')).filter(card => !card.classList.contains('pm-hidden-by-filter')).length;
        if (empty) empty.style.display = visibleCards ? 'none' : '';
    }

    function applyFilter(projectId, label) {
        activeProject = String(projectId || 'all');
        document.querySelectorAll('[data-project-filter]').forEach(btn => btn.classList.toggle('is-active', btn.dataset.projectFilter === activeProject));
        const selectedLabel = document.querySelector('[data-selected-label]');
        if (selectedLabel) {
            selectedLabel.textContent = label || (activeProject === 'all' ? 'Todos os projetos em aberto · milestone atual' : 'Projeto selecionado · milestone atual');
        }

        const selectedOpen = document.querySelector('[data-selected-open]');
        if (selectedOpen) {
            if (activeProject === 'all') {
                selectedOpen.style.display = 'none';
                selectedOpen.setAttribute('href', '#');
            } else {
                selectedOpen.style.display = '';
                selectedOpen.setAttribute('href', projectShowBase + '/' + activeProject);
            }
        }

        document.querySelectorAll('[data-project-id]').forEach(function(el){
            if (activeProject === 'all' || String(el.dataset.projectId) === activeProject) {
                el.classList.remove('pm-hidden-by-filter');
            } else {
                el.classList.add('pm-hidden-by-filter');
            }
        });

        document.querySelectorAll('[data-pm-matrix-zone]').forEach(refreshEmptyState);
        updateVisibleStats();
    }

    function visibleCount(selector) {
        return Array.from(document.querySelectorAll(selector)).filter(el => !el.classList.contains('pm-hidden-by-filter')).length;
    }

    function updateVisibleStats() {
        const totalMatrix = document.querySelector('[data-total-matrix]');
        if (totalMatrix) totalMatrix.textContent = visibleCount('.pm-matrix-task[data-task-id]');
    }

    document.querySelectorAll('[data-project-filter]').forEach(function(button){
        button.addEventListener('click', function(){
            const projectId = this.dataset.projectFilter;
            const projectLabel = this.dataset.projectLabel || (this.querySelector('strong') ? this.querySelector('strong').textContent : 'Projeto selecionado');
            applyFilter(projectId, projectId === 'all' ? 'Todos os projetos em aberto · milestone atual' : (projectLabel + ' · milestone atual'));
        });
    });

    const quickModal = document.querySelector('[data-quick-task-modal]');
    const quickProject = document.querySelector('[data-quick-project]');
    const quickMilestone = document.querySelector('[data-quick-milestone]');
    const quickParentTask = document.querySelector('[data-quick-parent-task]');

    function openQuickTaskModal() {
        if (!quickModal) return;
        quickModal.classList.add('is-open');
        quickModal.setAttribute('aria-hidden', 'false');
        if (activeProject !== 'all' && quickProject) {
            quickProject.value = activeProject;
            syncQuickTaskSelects();
        }
    }

    function closeQuickTaskModal() {
        if (!quickModal) return;
        quickModal.classList.remove('is-open');
        quickModal.setAttribute('aria-hidden', 'true');
    }

    function syncQuickTaskSelects() {
        const projectId = quickProject ? String(quickProject.value || '') : '';
        if (quickMilestone) {
            quickMilestone.innerHTML = '<option value="">Selecionar milestone</option>';
            (quickMilestones[projectId] || []).forEach(function(item){
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.title + (item.status ? ' · ' + item.status : '');
                quickMilestone.appendChild(option);
            });
        }
        syncQuickParentTasks();
    }

    function syncQuickParentTasks() {
        const projectId = quickProject ? String(quickProject.value || '') : '';
        const milestoneId = quickMilestone ? String(quickMilestone.value || '') : '';
        if (!quickParentTask) return;
        quickParentTask.innerHTML = '<option value="">Criar como task direta do milestone</option>';
        (quickParentTasks[projectId] || []).forEach(function(item){
            if (milestoneId && String(item.parent_id) !== milestoneId) return;
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = (item.milestone_title ? item.milestone_title + ' › ' : '') + item.title;
            quickParentTask.appendChild(option);
        });
    }

    document.querySelectorAll('[data-open-quick-task]').forEach(btn => btn.addEventListener('click', openQuickTaskModal));
    document.querySelectorAll('[data-close-quick-task]').forEach(btn => btn.addEventListener('click', closeQuickTaskModal));
    if (quickProject) quickProject.addEventListener('change', syncQuickTaskSelects);
    if (quickMilestone) quickMilestone.addEventListener('change', syncQuickParentTasks);
    if (quickModal) quickModal.addEventListener('click', function(event){ if (event.target === quickModal) closeQuickTaskModal(); });


    document.querySelectorAll('.pm-matrix-task[draggable="true"]').forEach(function(card){
        card.addEventListener('dragstart', function(){
            dragged = card;
            card.classList.add('is-dragging');
        });
        card.addEventListener('dragend', function(){
            card.classList.remove('is-dragging');
            dragged = null;
        });
    });

    document.querySelectorAll('[data-pm-matrix-zone]').forEach(function(zone){
        zone.addEventListener('dragover', function(event){
            event.preventDefault();
            zone.classList.add('is-over');
        });
        zone.addEventListener('dragleave', function(){
            zone.classList.remove('is-over');
        });
        zone.addEventListener('drop', function(event){
            event.preventDefault();
            zone.classList.remove('is-over');
            if (!dragged) return;

            const oldZone = dragged.closest('[data-pm-matrix-zone]');
            const list = zone.querySelector('[data-pm-matrix-list]');
            if (!list) return;

            list.appendChild(dragged);
            dragged.classList.remove('pm-hidden-by-filter');
            if (activeProject !== 'all' && String(dragged.dataset.projectId) !== activeProject) {
                dragged.classList.add('pm-hidden-by-filter');
            }

            const importance = parseInt(zone.dataset.importance || '3', 10);
            const urgency = parseInt(zone.dataset.urgency || '3', 10);
            const updateUrl = dragged.dataset.updateUrl;
            dragged.querySelector('[data-pm-importance]').innerHTML = '<i class="fa-solid fa-star"></i> ' + importance;
            dragged.querySelector('[data-pm-urgency]').innerHTML = '<i class="fa-solid fa-bolt"></i> ' + urgency;
            refreshEmptyState(zone);
            if (oldZone) refreshEmptyState(oldZone);

            setStatus('A guardar...', 'saving');
            fetch(updateUrl, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({importance, urgency})
            }).then(function(response){
                if (!response.ok) throw new Error('Erro ao guardar');
                return response.json();
            }).then(function(){
                setStatus('Guardado', 'saved');
            }).catch(function(){
                setStatus('Erro ao guardar', 'error');
            });
        });
        refreshEmptyState(zone);
    });

    document.querySelectorAll('[data-pm-task-status]').forEach(function(select){
        select.addEventListener('pointerdown', function(event){ event.stopPropagation(); });
        select.addEventListener('mousedown', function(event){ event.stopPropagation(); });
        select.addEventListener('dragstart', function(event){ event.preventDefault(); event.stopPropagation(); });
        select.addEventListener('change', function(){
            const card = select.closest('[data-task-id]');
            if (!card || !card.dataset.statusUrl) return;

            setStatus('A atualizar estado...', 'saving');
            fetch(card.dataset.statusUrl, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'},
                body: JSON.stringify({panel: select.value})
            }).then(function(response){
                if (!response.ok) throw new Error('Erro ao atualizar estado');
                return response.json();
            }).then(function(data){
                const label = select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent : data.status;
                const statusLine = card.querySelector('.pm-matrix-task-status');
                if (statusLine) {
                    statusLine.dataset.taskStatus = data.status || select.value;
                }
                card.classList.remove('is-task-next','is-task-execution','is-task-review','is-task-done');
                card.classList.add('is-task-' + select.value);
                setStatus('Estado atualizado: ' + label, 'saved');
            }).catch(function(){
                setStatus('Erro ao atualizar estado', 'error');
            });
        });
    });

    updateVisibleStats();
})();
</script>
@endsection
