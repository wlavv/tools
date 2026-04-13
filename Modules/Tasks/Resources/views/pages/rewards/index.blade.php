@extends('layouts.app')
@include('tasks::includes.css')
@include('tasks::includes.js')

@section('content')
<div class="container-fluid px-0">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-0">Prémios</h4>
                <div class="muted-small">Defaults globais / por membro e overrides mensais</div>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <form method="GET" action="{{ route('tasks.rewards.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                    <input type="number" name="year" class="form-control form-control-sm compact-input" value="{{ $year }}" style="width:100px;">
                    <input type="number" name="month" class="form-control form-control-sm compact-input" value="{{ $month }}" min="1" max="12" style="width:80px;">
                    <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-filter"></i></button>
                </form>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i></a>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0">Escalões default</h5>
                    <div class="muted-small">Sem membro = global. Com membro = específico do membro.</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0 tasks-table">
                    <thead>
                        <tr>
                            <th>Membro</th>
                            <th>%</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Ordem</th>
                            <th>Ativo</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="inline-create-row">
                            <td colspan="7">
                                <form method="POST" action="{{ route('tasks.rewards.default.store') }}" class="reward-line w-100">
                                    @csrf
                                    <select name="member_id" class="form-select form-select-sm compact-select reward-member">
                                        <option value="">Global</option>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" step="0.01" name="threshold_percent" class="form-control form-control-sm compact-input reward-threshold" placeholder="%" required>
                                    <input type="text" name="name" class="form-control form-control-sm compact-input reward-name" placeholder="Nome do prémio" required>
                                    <input type="text" name="description" class="form-control form-control-sm compact-input reward-desc" placeholder="Descrição">
                                    <input type="number" name="sort_order" class="form-control form-control-sm compact-input reward-order" value="0">
                                    <div class="text-center reward-active"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked></div>
                                    <div class="reward-actions text-end"><button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-plus"></i></button></div>
                                </form>
                            </td>
                        </tr>

                        @foreach($defaultRewards as $reward)
                            <tr>
                                <td colspan="7">
                                    <form method="POST" action="{{ route('tasks.rewards.default.update', $reward->id) }}" class="reward-line w-100">
                                        @csrf
                                        <select name="member_id" class="form-select form-select-sm compact-select reward-member">
                                            <option value="">Global</option>
                                            @foreach($members as $member)
                                                <option value="{{ $member->id }}" @selected((int)$reward->member_id === (int)$member->id)>{{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" name="threshold_percent" class="form-control form-control-sm compact-input reward-threshold" value="{{ $reward->threshold_percent }}" required>
                                        <input type="text" name="name" class="form-control form-control-sm compact-input reward-name" value="{{ $reward->name }}" required>
                                        <input type="text" name="description" class="form-control form-control-sm compact-input reward-desc" value="{{ $reward->description }}">
                                        <input type="number" name="sort_order" class="form-control form-control-sm compact-input reward-order" value="{{ $reward->sort_order }}">
                                        <div class="text-center reward-active"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($reward->is_active == 1)></div>
                                        <div class="reward-actions text-end d-flex gap-1 justify-content-end">
                                            <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeactivate('delete-reward-default-{{ $reward->id }}')"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </form>
                                    <form id="delete-reward-default-{{ $reward->id }}" method="POST" action="{{ route('tasks.rewards.default.delete', $reward->id) }}" class="d-none">@csrf</form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="mb-0">Overrides mensais</h5>
                    <div class="muted-small">Aplicados primeiro: por membro e mês, depois global do mês.</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0 tasks-table">
                    <thead>
                        <tr>
                            <th>Ano</th>
                            <th>Mês</th>
                            <th>Membro</th>
                            <th>%</th>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Ordem</th>
                            <th>Ativo</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="inline-create-row">
                            <td colspan="9">
                                <form method="POST" action="{{ route('tasks.rewards.override.store') }}" class="reward-line reward-line-override w-100">
                                    @csrf
                                    <input type="number" name="year" class="form-control form-control-sm compact-input reward-year" value="{{ $year }}" required>
                                    <input type="number" name="month" class="form-control form-control-sm compact-input reward-month" value="{{ $month }}" min="1" max="12" required>
                                    <select name="member_id" class="form-select form-select-sm compact-select reward-member">
                                        <option value="">Global</option>
                                        @foreach($members as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="number" step="0.01" name="threshold_percent" class="form-control form-control-sm compact-input reward-threshold" placeholder="%" required>
                                    <input type="text" name="name" class="form-control form-control-sm compact-input reward-name" placeholder="Nome do prémio" required>
                                    <input type="text" name="description" class="form-control form-control-sm compact-input reward-desc" placeholder="Descrição">
                                    <input type="number" name="sort_order" class="form-control form-control-sm compact-input reward-order" value="0">
                                    <div class="text-center reward-active"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked></div>
                                    <div class="reward-actions text-end"><button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-plus"></i></button></div>
                                </form>
                            </td>
                        </tr>

                        @foreach($monthOverrides as $override)
                            <tr>
                                <td colspan="9">
                                    <form method="POST" action="{{ route('tasks.rewards.override.update', $override->id) }}" class="reward-line reward-line-override w-100">
                                        @csrf
                                        <input type="number" name="year" class="form-control form-control-sm compact-input reward-year" value="{{ $override->year }}" required>
                                        <input type="number" name="month" class="form-control form-control-sm compact-input reward-month" value="{{ $override->month }}" min="1" max="12" required>
                                        <select name="member_id" class="form-select form-select-sm compact-select reward-member">
                                            <option value="">Global</option>
                                            @foreach($members as $member)
                                                <option value="{{ $member->id }}" @selected((int)$override->member_id === (int)$member->id)>{{ $member->name }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" name="threshold_percent" class="form-control form-control-sm compact-input reward-threshold" value="{{ $override->threshold_percent }}" required>
                                        <input type="text" name="name" class="form-control form-control-sm compact-input reward-name" value="{{ $override->name }}" required>
                                        <input type="text" name="description" class="form-control form-control-sm compact-input reward-desc" value="{{ $override->description }}">
                                        <input type="number" name="sort_order" class="form-control form-control-sm compact-input reward-order" value="{{ $override->sort_order }}">
                                        <div class="text-center reward-active"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($override->is_active == 1)></div>
                                        <div class="reward-actions text-end d-flex gap-1 justify-content-end">
                                            <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i></button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeactivate('delete-reward-override-{{ $override->id }}')"><i class="fa-solid fa-trash"></i></button>
                                        </div>
                                    </form>
                                    <form id="delete-reward-override-{{ $override->id }}" method="POST" action="{{ route('tasks.rewards.override.delete', $override->id) }}" class="d-none">@csrf</form>
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
