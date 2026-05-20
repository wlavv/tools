@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')
@php
    use Illuminate\Support\Str;

    $panelRoute = fn($task) => route('project_manager.projects.tasks.panel', [$project->id, $task->id]);
    $matrixRoute = fn($task) => route('project_manager.projects.tasks.priority_matrix', [$project->id, $task->id]);

    $matrixGroups = [
        'do_now' => ['label' => 'Fazer agora', 'hint' => 'Importante + urgente', 'importance' => 5, 'urgency' => 5, 'items' => collect(), 'icon' => 'fa-fire'],
        'plan' => ['label' => 'Planear', 'hint' => 'Importante + pouco urgente', 'importance' => 5, 'urgency' => 2, 'items' => collect(), 'icon' => 'fa-calendar-check'],
        'delegate' => ['label' => 'Delegar / simplificar', 'hint' => 'Urgente + menos importante', 'importance' => 2, 'urgency' => 5, 'items' => collect(), 'icon' => 'fa-bolt'],
        'later' => ['label' => 'Mais tarde', 'hint' => 'Pouco urgente + menos importante', 'importance' => 2, 'urgency' => 2, 'items' => collect(), 'icon' => 'fa-box-archive'],
    ];

    $matrixSourceTasks = isset($matrixTasks) ? $matrixTasks : $executionTasks;

    foreach ($matrixSourceTasks as $task) {
        $importance = (int)($task->importance ?? max(1, 6 - (int)($task->priority ?? 3)));
        $urgency = (int)($task->urgency ?? (in_array(($task->status ?? ''), ['in_progress', 'review']) ? 5 : 2));
        if ($importance >= 4 && $urgency >= 4) { $matrixGroups['do_now']['items']->push($task); }
        elseif ($importance >= 4) { $matrixGroups['plan']['items']->push($task); }
        elseif ($urgency >= 4) { $matrixGroups['delegate']['items']->push($task); }
        else { $matrixGroups['later']['items']->push($task); }
    }
@endphp

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @include('project-manager::partials.project-tabs', ['activeTab' => 'productivity'])

        <div class="pm-page-intro">
            <div>
                <div class="pm-page-kicker">Productivity board</div>
                <div class="pm-page-lead">Kanban para execução diária, Gantt operacional e matriz de Eisenhower com drag & drop.</div>
            </div>
            @if($activeMilestone)
                <div class="pm-context-pill"><i class="fa-solid fa-flag"></i> Milestone atual: <strong>{{ $activeMilestone->title }}</strong></div>
            @endif
        </div>

        <div class="pm-kanban pm-kanban--trello">
            <div class="pm-kanban-column" data-pm-panel="execution">
                <div class="pm-kanban-head"><i class="fa-solid fa-person-running"></i> Em execução <span>{{ $executionTasks->count() }}</span></div>
                @forelse($executionTasks as $task)
                    <article class="pm-kanban-card" data-pm-task-card="{{ $task->id }}">
                        <div class="pm-kanban-card-title">{{ $task->title }}</div>
                        <div class="pm-kanban-card-meta">{{ Str::limit($task->description ?? $task->comment ?? '', 95) }}</div>
                        <div class="pm-task-badges">
                            <span><i class="fa-solid fa-star"></i> I {{ $task->importance ?? 3 }}</span>
                            <span><i class="fa-solid fa-bolt"></i> U {{ $task->urgency ?? 3 }}</span>
                        </div>
                        <div class="pm-progress-mini"><span style="width: {{ in_array(($task->status ?? ''), ['review']) ? 85 : 55 }}%"></span></div>
                        <div class="pm-kanban-actions">
                            <form method="POST" action="{{ $panelRoute($task) }}">@csrf<input type="hidden" name="panel" value="next"><button class="pm-btn pm-btn--compact"><i class="fa-solid fa-arrow-left"></i> Seguintes</button></form>
                            <button type="button" class="pm-btn pm-btn--compact pm-btn--danger" data-pm-block-open data-task-id="{{ $task->id }}" data-task-title="{{ e($task->title) }}"><i class="fa-solid fa-ban"></i> Bloquear</button>
                            <form method="POST" action="{{ $panelRoute($task) }}">@csrf<input type="hidden" name="panel" value="done"><button class="pm-btn pm-btn--compact pm-btn--success"><i class="fa-solid fa-check"></i></button></form>
                        </div>
                    </article>
                @empty
                    <div class="pm-empty">Sem tarefas em execução.</div>
                @endforelse
            </div>

            <div class="pm-kanban-column" data-pm-panel="next">
                <div class="pm-kanban-head"><i class="fa-solid fa-forward-step"></i> Seguintes <span>{{ $nextTasks->count() }}</span></div>
                @forelse($nextTasks as $task)
                    <article class="pm-kanban-card" data-pm-task-card="{{ $task->id }}">
                        <div class="pm-kanban-card-title">{{ $task->title }}</div>
                        <div class="pm-kanban-card-meta">{{ $task->status ?? 'pending' }}</div>
                        <div class="pm-kanban-actions">
                            <form method="POST" action="{{ $panelRoute($task) }}">@csrf<input type="hidden" name="panel" value="execution"><button class="pm-btn pm-btn--compact pm-btn--primary"><i class="fa-solid fa-play"></i> Executar</button></form>
                            <button type="button" class="pm-btn pm-btn--compact pm-btn--danger" data-pm-block-open data-task-id="{{ $task->id }}" data-task-title="{{ e($task->title) }}"><i class="fa-solid fa-ban"></i></button>
                            <a class="pm-btn pm-btn--compact pm-btn--warning" href="{{ route('project_manager.projects.tasks.edit', [$project->id, $task->id]) }}"><i class="fa-solid fa-pencil"></i></a>
                        </div>
                    </article>
                @empty
                    <div class="pm-empty">Sem tarefas seguintes.</div>
                @endforelse
            </div>

            <div class="pm-kanban-column" data-pm-panel="blocked">
                <div class="pm-kanban-head"><i class="fa-solid fa-triangle-exclamation"></i> Bloqueadas <span>{{ $blockedTasks->count() }}</span></div>
                @forelse($blockedTasks as $task)
                    <article class="pm-kanban-card pm-kanban-card--blocked" data-pm-task-card="{{ $task->id }}">
                        <div class="pm-kanban-card-title">{{ $task->title }}</div>
                        <div class="pm-kanban-card-meta">{{ Str::limit($task->blocked_reason ?? $task->comment ?? '', 120) }}</div>
                        <div class="pm-kanban-actions">
                            <form method="POST" action="{{ $panelRoute($task) }}">@csrf<input type="hidden" name="panel" value="execution"><button class="pm-btn pm-btn--compact pm-btn--primary"><i class="fa-solid fa-unlock"></i> Retomar</button></form>
                            <a class="pm-btn pm-btn--compact pm-btn--warning" href="{{ route('project_manager.projects.tasks.edit', [$project->id, $task->id]) }}"><i class="fa-solid fa-pencil"></i></a>
                        </div>
                    </article>
                @empty
                    <div class="pm-empty">Sem bloqueios.</div>
                @endforelse
            </div>
        </div>

        <div class="pm-grid-2 mt-3">
            <div class="pm-card">
                <div class="pm-section-bar">
                    <div>
                        <div class="pm-card-title"><i class="fa-solid fa-chart-gantt"></i> Gantt operacional</div>
                        <div class="pm-card-subtitle mb-0">Vista gráfica simples das tasks com datas disponíveis.</div>
                    </div>
                </div>
                <div class="pm-gantt">
                    @forelse($ganttTasks as $task)
                        @php
                            $offset = max(0, min(80, ((int)($task->priority ?? 3) - 1) * 10));
                            $width = max(18, min(90, (int)($task->expected_time ?? 8) * 3));
                        @endphp
                        <div class="pm-gantt-row">
                            <div class="pm-gantt-label">{{ Str::limit($task->title, 28) }}</div>
                            <div class="pm-gantt-track"><span style="left: {{ $offset }}%; width: {{ $width }}%;"></span></div>
                        </div>
                    @empty
                        <div class="pm-empty">Sem tasks suficientes para Gantt.</div>
                    @endforelse
                </div>
            </div>

            <div class="pm-card">
                <div class="pm-section-bar">
                    <div>
                        <div class="pm-card-title"><i class="fa-solid fa-table-cells-large"></i> Matriz de Eisenhower</div>
                        <div class="pm-card-subtitle mb-0">As tasks abertas entram automaticamente na matriz. Depois arrasta entre quadrantes para ajustar importância e urgência.</div>
                    </div>
                    <span class="pm-save-indicator" data-pm-matrix-status>Pronto</span>
                </div>
                <div class="pm-eisenhower pm-eisenhower--dropzones">
                    @foreach($matrixGroups as $key => $group)
                        <div class="pm-eisenhower-cell" data-pm-matrix-zone="{{ $key }}" data-importance="{{ $group['importance'] }}" data-urgency="{{ $group['urgency'] }}">
                            <div class="pm-eisenhower-title"><i class="fa-solid {{ $group['icon'] }}"></i> {{ $group['label'] }}</div>
                            <div class="pm-muted pm-small mb-2">{{ $group['hint'] }}</div>
                            <div class="pm-matrix-list" data-pm-matrix-list>
                                @forelse($group['items'] as $task)
                                    <article class="pm-matrix-task" draggable="true" data-task-id="{{ $task->id }}" data-update-url="{{ $matrixRoute($task) }}" data-status-url="{{ $panelRoute($task) }}">
                                        <strong>{{ Str::limit($task->title, 48) }}</strong>
                                        <div class="pm-matrix-task-status">{{ $task->status ?? 'pending' }}</div>
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
        </div>
    </div>
</div>

<div class="pm-modal-backdrop" data-pm-block-modal>
    <div class="pm-modal-card">
        <div class="pm-section-bar">
            <div>
                <div class="pm-card-title"><i class="fa-solid fa-ban"></i> Mover para bloqueadas</div>
                <div class="pm-card-subtitle mb-0" data-pm-block-title></div>
            </div>
            <button type="button" class="pm-btn pm-btn--compact" data-pm-block-close><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" data-pm-block-form>
            @csrf
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <label class="pm-form-label">Tipo de bloqueio</label>
                    <select name="block_type" class="form-control" required>
                        <option value="technical_issue">Problema técnico</option>
                        <option value="missing_information">Informação em falta</option>
                        <option value="external_dependency">Dependência externa</option>
                        <option value="decision_needed">Decisão pendente</option>
                        <option value="bug">Bug</option>
                        <option value="access_required">Acesso necessário</option>
                        <option value="database_issue">Problema de base de dados</option>
                        <option value="other">Outro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="pm-form-label">Dependência relacionada</label>
                    <select name="dependency_id" class="form-control">
                        <option value="">Sem dependência específica</option>
                        @foreach($dependencies as $dependency)
                            <option value="{{ $dependency->id }}">#{{ $dependency->id }} · {{ $dependency->dependency_type ?? 'dependency' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="pm-form-label">Razão do bloqueio</label>
                    <textarea name="blocked_reason" class="form-control" rows="4" required placeholder="Explica claramente o que bloqueia a task e o que é necessário para desbloquear."></textarea>
                </div>
            </div>
            <div class="pm-detail-actions-footer">
                <button type="button" class="pm-btn" data-pm-block-close>Cancelar</button>
                <button type="submit" class="pm-btn pm-btn--danger"><i class="fa-solid fa-triangle-exclamation"></i> Bloquear task</button>
            </div>
        </form>
    </div>
</div>

<script>
(function(){
    const csrf = '{{ csrf_token() }}';

    const modal = document.querySelector('[data-pm-block-modal]');
    const form = document.querySelector('[data-pm-block-form]');
    const title = document.querySelector('[data-pm-block-title]');
    if (modal && form) {
        document.querySelectorAll('[data-pm-block-open]').forEach(function(button){
            button.addEventListener('click', function(){
                const taskId = this.dataset.taskId;
                form.action = '{{ url(config('project-manager.route_prefix', 'project-manager') . '/projects/' . $project->id . '/tasks') }}/' + taskId + '/block';
                title.textContent = this.dataset.taskTitle || '';
                modal.classList.add('is-visible');
            });
        });
        document.querySelectorAll('[data-pm-block-close]').forEach(function(button){
            button.addEventListener('click', function(){ modal.classList.remove('is-visible'); });
        });
    }

    const status = document.querySelector('[data-pm-matrix-status]');
    let dragged = null;

    function setStatus(text, mode) {
        if (!status) return;
        status.textContent = text;
        status.dataset.mode = mode || 'idle';
    }

    function refreshEmptyState(zone) {
        const list = zone.querySelector('[data-pm-matrix-list]');
        if (!list) return;
        const empty = list.querySelector('[data-pm-empty]');
        const hasCards = !!list.querySelector('[data-task-id]');
        if (empty) empty.style.display = hasCards ? 'none' : '';
    }

    document.querySelectorAll('.pm-matrix-task[draggable="true"]').forEach(function(card){
        card.addEventListener('dragstart', function(event){
            dragged = card;
            card.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', card.dataset.taskId || '');
        });
        card.addEventListener('dragend', function(){
            card.classList.remove('is-dragging');
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
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify({ panel: select.value })
            }).then(function(response){
                if (!response.ok) throw new Error('Erro ao atualizar estado');
                return response.json();
            }).then(function(){
                const label = select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent : select.value;
                card.classList.remove('is-task-next','is-task-execution','is-task-review','is-task-done');
                card.classList.add('is-task-' + select.value);
                setStatus('Estado atualizado: ' + label, 'saved');
                setTimeout(function(){ setStatus('Pronto', 'idle'); }, 1200);
            }).catch(function(){
                setStatus('Erro ao atualizar estado', 'error');
            });
        });
    });

    document.querySelectorAll('[data-pm-matrix-zone]').forEach(function(zone){ zone.classList.remove('is-drag-over'); });
        });
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
                headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},
                body: JSON.stringify({ panel: select.value })
            }).then(function(response){
                if (!response.ok) throw new Error('Erro ao atualizar estado');
                return response.json();
            }).then(function(){
                const label = select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent : select.value;
                card.classList.remove('is-task-next','is-task-execution','is-task-review','is-task-done');
                card.classList.add('is-task-' + select.value);
                setStatus('Estado atualizado: ' + label, 'saved');
                setTimeout(function(){ setStatus('Pronto', 'idle'); }, 1200);
            }).catch(function(){
                setStatus('Erro ao atualizar estado', 'error');
            });
        });
    });

    document.querySelectorAll('[data-pm-matrix-zone]').forEach(function(zone){
        refreshEmptyState(zone);

        zone.addEventListener('dragover', function(event){
            if (!dragged) return;
            event.preventDefault();
            zone.classList.add('is-drag-over');
        });

        zone.addEventListener('dragleave', function(event){
            if (!zone.contains(event.relatedTarget)) {
                zone.classList.remove('is-drag-over');
            }
        });

        zone.addEventListener('drop', function(event){
            event.preventDefault();
            zone.classList.remove('is-drag-over');
            if (!dragged) return;

            const previousZone = dragged.closest('[data-pm-matrix-zone]');
            const list = zone.querySelector('[data-pm-matrix-list]');
            const importance = parseInt(zone.dataset.importance || '3', 10);
            const urgency = parseInt(zone.dataset.urgency || '3', 10);
            const updateUrl = dragged.dataset.updateUrl;

            if (!list || !updateUrl) return;

            list.appendChild(dragged);
            refreshEmptyState(zone);
            if (previousZone) refreshEmptyState(previousZone);

            dragged.querySelector('[data-pm-importance]').innerHTML = '<i class="fa-solid fa-star"></i> ' + importance;
            dragged.querySelector('[data-pm-urgency]').innerHTML = '<i class="fa-solid fa-bolt"></i> ' + urgency;

            setStatus('A guardar...', 'saving');

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ importance: importance, urgency: urgency })
            }).then(function(response){
                if (!response.ok) throw new Error('Erro ao guardar');
                return response.json();
            }).then(function(){
                setStatus('Guardado', 'saved');
                setTimeout(function(){ setStatus('Pronto', 'idle'); }, 1200);
            }).catch(function(){
                if (previousZone) {
                    const previousList = previousZone.querySelector('[data-pm-matrix-list]');
                    if (previousList) previousList.appendChild(dragged);
                    refreshEmptyState(previousZone);
                    refreshEmptyState(zone);
                }
                setStatus('Erro ao guardar', 'error');
            });
        });
    });
})();
</script>
@endsection
