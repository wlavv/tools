@extends('layouts.app')
@include('tasks::includes.css')
@include('tasks::includes.js')

@section('content')
<div class="lsg-content px-0">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="mb-0">Dashboard de Tarefas</h3>
                <div class="muted-small">{{ sprintf('%02d/%04d', $month, $year) }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i></a>
                <a href="{{ route('tasks.calendar', [$year, $month]) }}" class="btn btn-outline-primary"><i class="fa-solid fa-calendar-days"></i></a>
            </div>
        </div>
    </div>

    <div class="stats-cards mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="muted-small">Registos do mês</div>
                <div class="stats-value">{{ $stats['totals']['rows'] }}</div>
            </div>
        </div>
        <div class="card stats-card">
            <div class="card-body">
                <div class="muted-small">Concluídas</div>
                <div class="stats-value text-success">{{ $stats['totals']['done'] }}</div>
            </div>
        </div>
        <div class="card stats-card">
            <div class="card-body">
                <div class="muted-small">Pendentes</div>
                <div class="stats-value text-warning">{{ $stats['totals']['pending'] }}</div>
            </div>
        </div>
        <div class="card stats-card">
            <div class="card-body">
                <div class="muted-small">Total valor</div>
                <div class="stats-value text-success">{{ number_format($stats['totals']['value'], 2, ',', '.') }} €</div>
            </div>
        </div>
    </div>

    <div class="dashboard-insights-grid mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="muted-small mb-2">Ranking de consistência</div>
                <div class="card-subtle">
                    @foreach(collect($stats['rankings']['consistency'] ?? [])->take(3) as $row)
                        <div class="calendar-task-item">
                            <span>{{ $loop->iteration }}. {{ $row->name }}</span>
                            <span>{{ $row->current_streak }} dia(s)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card h-100">
            <div class="card-body">
                <div class="muted-small mb-2">Ranking de conclusão</div>
                <div class="card-subtle">
                    @foreach(collect($stats['rankings']['completion'] ?? [])->take(3) as $row)
                        <div class="calendar-task-item">
                            <span>{{ $loop->iteration }}. {{ $row->name }}</span>
                            <span>{{ number_format($row->completion_percent, 1, ',', '.') }}%</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card h-100">
            <div class="card-body">
                <div class="muted-small mb-2">Melhor dia do mês</div>
                @php
                    $bestDay = $stats['best_day'] ?? null;
                    $bestPercent = $bestDay && (int)($bestDay->total_count ?? 0) > 0 ? round(((int)$bestDay->done_count / (int)$bestDay->total_count) * 100, 1) : null;
                @endphp
                <div class="summary-value">{{ $bestDay ? \Carbon\Carbon::parse($bestDay->date)->format('d/m') : '--' }}</div>
                <div class="muted-small">{{ $bestDay ? ((int)$bestDay->done_count.' de '.(int)$bestDay->total_count.' tarefas · '.number_format($bestPercent, 1, ',', '.').'%') : 'Sem dados suficientes' }}</div>
            </div>
        </div>
    </div>

    <div class="dashboard-member-grid">
        @foreach($stats['members'] as $member)
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">{{ $member->name }}</h5>
                            <div class="muted-small">{{ number_format($member->completion_percent, 1, ',', '.') }}% do mês concluído</div>
                        </div>
                        <div class="task-status-pill">{{ number_format($member->completion_percent, 1, ',', '.') }}%</div>
                    </div>

                    <div class="stats-cards stats-cards--compact mb-3">
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="muted-small">Progresso</div>
                                <div class="stats-value">{{ $member->progress_label }}</div>
                            </div>
                        </div>
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="muted-small">Valor</div>
                                <div class="stats-value {{ $member->total_value >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($member->total_value, 2, ',', '.') }} €</div>
                            </div>
                        </div>
                    </div>

                    <div class="gamification-grid mb-3">
                        <div class="mini-kpi">
                            <div class="muted-small">Medalha</div>
                            <div class="mini-kpi-value medal-pill {{ $member->medal->class ?? 'is-base' }}"><i class="{{ $member->medal->icon ?? 'fa-regular fa-flag' }}"></i> <span>{{ $member->medal->label ?? 'Arranque' }}</span></div>
                        </div>
                        <div class="mini-kpi">
                            <div class="muted-small">Streak atual</div>
                            <div class="mini-kpi-value">{{ $member->current_streak }} dia(s)</div>
                        </div>
                        <div class="mini-kpi">
                            <div class="muted-small">Melhor streak</div>
                            <div class="mini-kpi-value">{{ $member->best_streak }} dia(s)</div>
                        </div>
                        <div class="mini-kpi">
                            <div class="muted-small">Dias completos</div>
                            <div class="mini-kpi-value">{{ $member->completed_days }}/{{ $member->active_days }}</div>
                        </div>
                    </div>

                    @if(collect($member->week_series ?? [])->isNotEmpty())
                        <div class="sparkline-card mb-3">
                            <div class="muted-small mb-2">Últimos 7 dias</div>
                            <div class="sparkline-bars">
                                @foreach($member->week_series as $day)
                                    @php $barPercent = (float)($day->percent ?? 0); @endphp
                                    <div class="sparkline-bar-wrap" title="{{ $day->label }} · {{ $day->done_count }}/{{ $day->total_count }}">
                                        <div class="sparkline-bar {{ ($day->completed_day ?? false) ? 'is-perfect' : '' }}" style="height: {{ max($barPercent, 8) }}%"></div>
                                        <span>{{ $day->weekday }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="reward-box {{ $member->reward_achieved ? 'is-achieved' : '' }} mb-3">
                        <div>
                            <div class="muted-small mb-1">Prémio atual</div>
                            <div class="reward-title">
                                @if($member->reward_achieved)
                                    {{ $member->reward_achieved->name }}
                                @else
                                    Ainda sem prémio
                                @endif
                            </div>
                        </div>
                        <div class="reward-progress text-end">
                            @if($member->reward_next)
                                <div class="muted-small mb-1">Próximo prémio</div>
                                <div class="fw-semibold">{{ $member->reward_next->name }}</div>
                                <div class="small opacity-75">Faltam {{ $member->remaining_tasks_for_next }} tarefas</div>
                            @else
                                <div class="fw-semibold">Escalão máximo</div>
                                <div class="small opacity-75">Objetivo do mês concluído</div>
                            @endif
                        </div>
                    </div>

                    <div class="card-subtle">
                        @foreach($member->reward_levels as $level)
                            <div class="calendar-task-item">
                                <span>{{ number_format($level->threshold_percent, 0, ',', '.') }}% · {{ $level->name }}</span>
                                <span class="{{ $member->completion_percent >= $level->threshold_percent ? 'text-success' : 'muted-small' }}">
                                    {{ $member->completion_percent >= $level->threshold_percent ? 'Atingido' : 'Por atingir' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
