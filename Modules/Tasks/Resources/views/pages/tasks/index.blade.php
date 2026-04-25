@extends('layouts.app')
@include('tasks::includes.css')
@include('tasks::includes.js')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="mb-0">Tarefas</h4>
                    <div class="muted-small">Periodicidade, impacto no progresso e valor acumulado</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm tasks-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Registo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="inline-create-row">
                            <td>
                                <form method="POST" action="{{ route('tasks.manage.store') }}" class="task-line w-100">
                                    @csrf
                                    <input type="number" name="sort_order" class="form-control form-control-sm compact-input" value="0" placeholder="Ordem">
                                    <input style="width: 200px !important;" type="text" name="task" class="form-control form-control-sm compact-input" placeholder="Nova tarefa" required>
                                    <select name="member_id" class="form-select form-select-sm compact-select" required>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <select name="frequency" class="form-select form-select-sm compact-select">
                                        <option value="daily">Diária</option>
                                        <option value="weekly">Semanal</option>
                                        <option value="monthly">Mensal</option>
                                    </select>
                                    <div class="inline-days compact-cell">
                                        @foreach(\Modules\Tasks\Models\Task::weekdayOptions() as $dayNum => $dayLabel)
                                            <label><input type="checkbox" name="days_mask[]" value="{{ $dayNum }}">{{ $dayLabel }}</label>
                                        @endforeach
                                    </div>
                                    <input type="number" min="1" max="31" name="monthly_day" class="form-control form-control-sm compact-input" placeholder="Dia mês">
                                    <select name="counts_for_completion" class="form-select form-select-sm compact-select">
                                        <option value="1">Conta</option>
                                        <option value="0">Não conta</option>
                                    </select>
                                    <select name="value_mode" class="form-select form-select-sm compact-select">
                                        <option value="add">Soma</option>
                                        <option value="subtract">Desconta</option>
                                    </select>
                                    <input type="number" step="0.01" name="value" class="form-control form-control-sm compact-input" value="0">
                                    <div class="text-center"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked></div>
                                    <button class="btn btn-sm btn-outline-success" title="Adicionar"><i class="fa-solid fa-plus"></i></button>
                                </form>
                            </td>
                        </tr>

                        @foreach($tasksList as $task)
                            @php $selectedDays = $task->selectedDays(); $selectedDayLabels = $task->selectedDaysLabels(); @endphp
                            <tr>
                                <td>
                                    <form method="POST" action="{{ route('tasks.manage.update', $task->id) }}" class="task-line w-100" style="margin-bottom: 0;">
                                        @csrf
                                        <input type="number" class="form-control form-control-sm compact-input" name="sort_order" value="{{ $task->sort_order }}">
                                        <input style="width: 200px !important;" type="text" class="form-control form-control-sm compact-input" name="task" value="{{ $task->task }}" required>
                                        <select name="member_id" class="form-select form-select-sm compact-select">
                                            @foreach($members as $member)
                                                <option value="{{ $member->id }}" @selected($task->member_id == $member->id)>{{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                        <select name="frequency" class="form-select form-select-sm compact-select">
                                            <option value="daily" @selected(($task->frequency ?? 'daily') === 'daily')>Diária</option>
                                            <option value="weekly" @selected(($task->frequency ?? 'daily') === 'weekly')>Semanal</option>
                                            <option value="monthly" @selected(($task->frequency ?? 'daily') === 'monthly')>Mensal</option>
                                        </select>
                                        <div class="compact-cell days-cell">
                                            <div class="inline-days">
                                                @foreach(\Modules\Tasks\Models\Task::weekdayOptions() as $dayNum => $dayLabel)
                                                    <label><input type="checkbox" name="days_mask[]" value="{{ $dayNum }}" @checked(in_array($dayNum, $selectedDays, true))>{{ $dayLabel }}</label>
                                                @endforeach
                                            </div>
                                        </div>
                                        <input type="number" min="1" max="31" class="form-control form-control-sm compact-input" name="monthly_day" value="{{ $task->monthly_day }}" placeholder="Dia mês">
                                        <select name="counts_for_completion" class="form-select form-select-sm compact-select">
                                            <option value="1" @selected((int)($task->counts_for_completion ?? 1) === 1)>Conta</option>
                                            <option value="0" @selected((int)($task->counts_for_completion ?? 1) === 0)>Não conta</option>
                                        </select>
                                        <select name="value_mode" class="form-select form-select-sm compact-select">
                                            <option value="add" @selected(($task->value_mode ?? 'add') === 'add')>Soma</option>
                                            <option value="subtract" @selected(($task->value_mode ?? 'add') === 'subtract')>Desconta</option>
                                        </select>
                                        <input type="number" step="0.01" class="form-control form-control-sm compact-input" name="value" value="{{ $task->value }}">
                                        <div class="text-center"><input type="checkbox" class="" name="is_active" value="1" @checked($task->is_active == 1)></div>
                                        <button class="btn btn-sm btn-outline-primary" title="Guardar"><i class="fa-solid fa-floppy-disk"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
