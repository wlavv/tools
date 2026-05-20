@extends('layouts.app')
@include('tasks::includes.css')
@include('tasks::includes.js')

@section('content')
<div class="lsg-content px-0">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="mb-0">Membros</h4>
                    <div class="muted-small">Gestão compacta dos membros da família</div>
                </div>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i></a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-sm tasks-table align-middle mb-0">
                    <thead>
                        <tr><th>Registo</th></tr>
                    </thead>
                    <tbody>
                        <tr class="inline-create-row">
                            <td>
                                <form method="POST" action="{{ route('tasks.members.store') }}" class="member-line w-100">
                                    @csrf
                                    <input type="text" name="name" class="form-control form-control-sm compact-input" placeholder="Novo membro" required>
                                    <select name="task_type" class="form-select form-select-sm compact-select">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                    </select>
                                    <input type="text" name="color" class="form-control form-control-sm compact-input" placeholder="#0d6efd">
                                    <input type="number" name="sort_order" class="form-control form-control-sm compact-input" value="0">
                                    <div class="text-center"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked></div>
                                    <div class="task-status-pill">slug automático</div>
                                    <button class="btn btn-sm btn-outline-success" title="Adicionar"><i class="fa-solid fa-plus"></i></button>
                                    <div></div>
                                </form>
                            </td>
                        </tr>

                        @foreach($members as $member)
                            <tr>
                                <td>
                                    <form method="POST" action="{{ route('tasks.members.update', $member->id) }}" class="member-line w-100">
                                        @csrf
                                        <input type="text" class="form-control form-control-sm compact-input" name="name" value="{{ $member->name }}" required>
                                        <select name="task_type" class="form-select form-select-sm compact-select">
                                            <option value="1" @selected($member->task_type==1)>1</option>
                                            <option value="2" @selected($member->task_type==2)>2</option>
                                        </select>
                                        <input type="text" class="form-control form-control-sm compact-input" name="color" value="{{ $member->color }}" placeholder="#0d6efd">
                                        <input type="number" class="form-control form-control-sm compact-input" name="sort_order" value="{{ $member->sort_order }}">
                                        <div class="text-center"><input type="checkbox" class="form-check-input" name="is_active" value="1" @checked($member->is_active == 1)></div>
                                        <div class="task-status-pill">{{ $member->slug }}</div>
                                        <button class="btn btn-sm btn-outline-primary" title="Guardar"><i class="fa-solid fa-floppy-disk"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeactivate('delete-member-{{ $member->id }}')"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                    <form id="delete-member-{{ $member->id }}" method="POST" action="{{ route('tasks.members.delete', $member->id) }}" class="d-none">@csrf</form>
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
