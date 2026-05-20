@extends('layouts.app')
@include('tasks::includes.css')
@include('tasks::includes.js')

@section('content')
<div class="lsg-content px-0">
    <div class="card mb-3">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                <div>
                    <h3 class="mb-1">Tasks</h3>
                    <div class="muted-small">Execução diária das tarefas da família</div>
                </div>
                <div class="task-day-pill">
                    <i class="fa-solid fa-calendar-day"></i>
                    <span>{{ now()->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>
            </div>

            <div class="tasks-member-nav" role="tablist" aria-label="Membros">
                @foreach($members as $member)
                    @php
                        $safe = $memberKeys[$member->name] ?? $member->slug;
                        $memberStat = $monthStatsByName[$member->name] ?? null;
                        $done = (int)($memberStat->total_done ?? 0);
                        $total = (int)($memberStat->total_rows ?? 0);
                        $percent = (float)($memberStat->completion_percent ?? 0);
                    @endphp
                    <button
                        type="button"
                        class="tasks-member-chip {{ $loop->first ? 'active' : '' }}"
                        data-key="{{ $safe }}"
                        onclick="tasksSelectPanel('{{ $safe }}')"
                        style="border-left:4px solid {{ $member->color ?: '#0d6efd' }};"
                    >
                        <span class="member-chip-name">{{ $member->name }}</span>
                        <span class="member-chip-progress">{{ $done }} / {{ $total }}</span>
                        <span class="member-chip-percent">{{ number_format($percent, 0) }}%</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    @foreach($tasks as $name => $group)
        @php
            $safe = $memberKeys[$name] ?? \Illuminate\Support\Str::slug(\Illuminate\Support\Str::ascii($name), '_');
            $memberStat = $monthStatsByName[$name] ?? null;
            $totalRows = (int)($memberStat->total_rows ?? 0);
            $totalDone = (int)($memberStat->total_done ?? 0);
            $completionPercent = (float)($memberStat->completion_percent ?? 0);
            $rewardAchieved = $memberStat->reward_achieved ?? null;
            $rewardNext = $memberStat->reward_next ?? null;
            $remainingNext = $memberStat->remaining_tasks_for_next ?? null;
            $memberValue = (float)($memberStat->total_value ?? 0);
            $currentStreak = (int)($memberStat->current_streak ?? 0);
            $medal = $memberStat->medal ?? (object)['label' => 'Arranque', 'icon' => 'fa-regular fa-flag', 'class' => 'is-base'];
            $eligibleToday = $group->filter(fn($task) => $task->countsForCompletion());
            $penaltyToday = $group->filter(fn($task) => !$task->countsForCompletion());
            $todayTotal = $eligibleToday->count();
            $todayDone = $eligibleToday->filter(fn($task) => isset($todayDoneMap[$task->id]) && (int)$todayDoneMap[$task->id]->done === 1)->count();
        @endphp

        <div class="tasks-panel" data-key="{{ $safe }}" @if(!$loop->first) style="display:none;" @endif>
            <div class="card mb-3">
                <div class="card-body p-2 p-md-3">
                    <div class="tasks-strip mb-3">
                        <div class="tasks-strip-badges">
                            <span class="task-status-pill task-status-pill--accent current-member-month-label-{{ $safe }}">{{ $totalDone }} de {{ $totalRows }} tarefas</span>
                            <span class="task-status-pill current-member-percent-{{ $safe }}">{{ number_format($completionPercent, 1, ',', '.') }}%</span>
                            <span class="task-status-pill current-member-today-{{ $safe }}">Hoje {{ $todayDone }} de {{ $todayTotal }}</span>
                            <span class="task-status-pill current-member-value-{{ $safe }}">{{ number_format($memberValue, 2, ',', '.') }} €</span>
                            <span class="task-status-pill medal-pill {{ $medal->class ?? 'is-base' }} current-member-medal-{{ $safe }}">
                                <i class="{{ $medal->icon ?? 'fa-regular fa-flag' }}"></i>
                                <span>{{ $medal->label ?? 'Arranque' }}</span>
                            </span>
                            <span class="task-status-pill current-member-streak-{{ $safe }}">Streak {{ $currentStreak }} dia(s)</span>
                            <span class="task-status-pill current-member-reward-achieved-{{ $safe }}">Prémio: {{ $rewardAchieved->name ?? '—' }}</span>
                            <span class="task-status-pill current-member-reward-next-{{ $safe }}">Próximo: {{ $rewardNext->name ?? 'Máximo' }}</span>
                            <span class="task-status-pill current-member-reward-remaining-{{ $safe }}">{{ $rewardNext ? 'Faltam '.(int)$remainingNext.' tarefa(s)' : 'Objetivo máximo concluído' }}</span>
                        </div>

                        <div class="progress tasks-progress tasks-progress--slim" role="progressbar" aria-valuenow="{{ $completionPercent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar current-member-progress-bar-{{ $safe }}" style="width: {{ $completionPercent }}%"></div>
                        </div>
                    </div>

                    @if($eligibleToday->isNotEmpty())
                        <div class="section-header mb-2">
                            <h5 class="mb-0">Tarefas do dia</h5>
                            <span class="task-status-pill">{{ $todayDone }} / {{ $todayTotal }}</span>
                        </div>

                        <div class="task-list task-list--compact mb-3">
                            @foreach($eligibleToday as $task)
                                @php
                                    $doneInfo = $todayDoneMap[$task->id] ?? null;
                                    $currentDone = (int)($doneInfo->done ?? 0);
                                    $currentValue = (float)($doneInfo->value ?? 0);
                                    $baseValue = (float)($task->value ?? 0);
                                @endphp
                                <div class="task-card task-row {{ $currentDone === 1 ? 'is-done' : 'is-pending' }}"
                                     data-task-id="{{ $task->id }}"
                                     data-counts-for-completion="1"
                                     data-value-mode="{{ $task->value_mode ?? 'add' }}"
                                     data-base-value="{{ abs($baseValue) }}"
                                     data-member-key="{{ $safe }}">
                                    <div class="task-row-main">
                                        @if(!empty($task->image))
                                            <div class="task-thumb-wrap">
                                                <img style="height: 80px;" src="{{ asset($task->image) }}" alt="{{ $task->task }}" class="task-thumb">
                                            </div>
                                        @endif

                                        <div class="task-copy">
                                            <div class="task-row-top"> <p class="task-title mb-0">{{ $task->task }}</p> </div>
                                        </div>
                                    </div>

                                    <div class="task-toggle task-toggle--compact" role="group" aria-label="Estado da tarefa {{ $task->task }}">
                                        <button type="button" class="task-toggle-btn is-success {{ $currentDone === 1 ? 'active' : '' }}" onclick="saveTaskState(this, {{ $task->id }}, 1)">
                                            <i class="fa-solid fa-check"></i><span>Feita</span>
                                        </button>
                                        <button type="button" class="task-toggle-btn is-danger {{ $currentDone === 0 ? 'active' : '' }}" onclick="saveTaskState(this, {{ $task->id }}, 0)">
                                            <i class="fa-solid fa-xmark"></i><span>Não</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($penaltyToday->isNotEmpty())
                        <div class="section-header mb-2">
                            <h5 class="mb-0">Penalizações do dia</h5>
                            <span class="task-status-pill">Não contam para prémio</span>
                        </div>

                        <div class="task-list task-list--compact">
                            @foreach($penaltyToday as $task)
                                @php
                                    $doneInfo = $todayDoneMap[$task->id] ?? null;
                                    $currentDone = (int)($doneInfo->done ?? 0);
                                    $currentValue = (float)($doneInfo->value ?? 0);
                                    $baseValue = (float)($task->value ?? 0);
                                @endphp
                                <div class="task-card task-row task-card--penalty {{ $currentDone === 1 ? 'is-done' : 'is-pending' }}"
                                     data-task-id="{{ $task->id }}"
                                     data-counts-for-completion="0"
                                     data-value-mode="{{ $task->value_mode ?? 'subtract' }}"
                                     data-base-value="{{ abs($baseValue) }}"
                                     data-member-key="{{ $safe }}">
                                    <div class="task-row-main">
                                        @if(!empty($task->image))
                                            <div class="task-thumb-wrap">
                                                <img style="height: 80px;" src="{{ asset($task->image) }}" alt="{{ $task->task }}" class="task-thumb">
                                            </div>
                                        @endif

                                        <div class="task-copy">
                                            <div class="task-row-top">
                                                <p class="task-title mb-0">{{ $task->task }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="task-toggle task-toggle--compact" role="group" aria-label="Estado da tarefa {{ $task->task }}">
                                        <button type="button" class="task-toggle-btn is-success {{ $currentDone === 1 ? 'active' : '' }}" onclick="saveTaskState(this, {{ $task->id }}, 1)">
                                            <i class="fa-solid fa-check"></i><span>Aplicar</span>
                                        </button>
                                        <button type="button" class="task-toggle-btn is-danger {{ $currentDone === 0 ? 'active' : '' }}" onclick="saveTaskState(this, {{ $task->id }}, 0)">
                                            <i class="fa-solid fa-rotate-left"></i><span>Remover</span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if($eligibleToday->isEmpty() && $penaltyToday->isEmpty())
                        <div class="alert alert-info mb-0">Sem tarefas previstas para hoje.</div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
window.tasksPageStats = @json($tasksPageStats);
</script>
@endsection
