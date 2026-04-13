@extends('layouts.app')
@include('tasks::includes.css')
@include('tasks::includes.js')

@section('content')
@php $monthlyTotals = []; @endphp
<div class="container-fluid px-0">
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="mb-0">Calendário de Tarefas</h3>
                <div class="muted-small">{{ sprintf('%02d/%04d', $month, $year) }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i></a>
                <a href="{{ route('tasks.dashboard', [$year, $month]) }}" class="btn btn-outline-primary"><i class="fa-solid fa-chart-column"></i></a>
            </div>
        </div>
    </div>

    <div class="calendar-grid">
        @forelse($calendar as $day => $users)
            @php $dayKey = \Carbon\Carbon::parse($day)->format('Ymd'); @endphp
            <div class="card day-card h-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <strong>{{ \Carbon\Carbon::parse($day)->format('d') }}</strong>
                            <span class="text-primary text-capitalize">{{ \Carbon\Carbon::parse($day)->translatedFormat('l') }}</span>
                        </div>
                        <div class="task-status-pill">{{ count($users) }} membros</div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="calendar-member-tabs">
                        @foreach($users as $user => $tasks)
                            @php $memberKey = \Illuminate\Support\Str::slug(\Illuminate\Support\Str::ascii($user), '_'); @endphp
                            <button type="button" class="calendar-member-tab {{ $loop->first ? 'active' : '' }}" data-day="{{ $dayKey }}" data-member="{{ $memberKey }}" data-role="calendar-tab" onclick="calendarSelectMember('{{ $dayKey }}','{{ $memberKey }}')">{{ $user }}</button>
                        @endforeach
                    </div>

                    @foreach($users as $user => $tasks)
                        @php $memberKey = \Illuminate\Support\Str::slug(\Illuminate\Support\Str::ascii($user), '_'); $dailyTotal = 0; $doneCount = 0; @endphp
                        <div class="calendar-member-panel {{ $loop->first ? 'active' : '' }}" data-day="{{ $dayKey }}" data-member="{{ $memberKey }}" data-role="calendar-panel">
                            @foreach($tasks as $task)
                                @php
                                    if (!empty($task['done'])) { $doneCount++; }
                                    if ($task['type'] == 2) {
                                        $dailyTotal += $task['value'];
                                        $monthlyTotals[$user] = ($monthlyTotals[$user] ?? 0) + $task['value'];
                                    }
                                @endphp
                            @endforeach
                            <div class="task-summary-row mb-2">
                                <span class="task-status-pill">{{ $doneCount }}/{{ count($tasks) }} feitas</span>
                                <span class="task-status-pill {{ $dailyTotal >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($dailyTotal, 2, ',', '.') }} €</span>
                            </div>
                            <div class="card-subtle">
                                @foreach($tasks as $task)
                                    <div class="calendar-task-item">
                                        <span>{!! $task['done'] ? '✅' : '❌' !!} {{ $task['name'] }}</span>
                                        @if($task['type'] == 2)
                                            <span>{{ number_format($task['value'], 2, ',', '.') }} €</span>
                                        @else
                                            <span class="muted-small">Tipo {{ $task['type'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="alert alert-info">Não existem tarefas para este mês.</div>
        @endforelse
    </div>

    @if(!empty($monthlyTotals))
        <div class="card mt-3">
            <div class="card-body">
                <h4 class="mb-3">Total do mês por utilizador</h4>
                <div class="stats-cards">
                    @foreach($monthlyTotals as $user => $total)
                        <div class="card stats-card">
                            <div class="card-body">
                                <div class="muted-small">{{ $user }}</div>
                                <div class="stats-value {{ $total >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($total, 2, ',', '.') }} €</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
