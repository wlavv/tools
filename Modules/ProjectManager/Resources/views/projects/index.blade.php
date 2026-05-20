@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::partials.styles')

<div class="lsg-content pm-wrap">
    <div class="pm-shell">
        @if(session('success'))
            <div class="pm-alert">{{ session('success') }}</div>
        @endif

        <div class="pm-card">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Pesquisar</label>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Nome, slug ou descrição">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <input class="form-control" name="status" value="{{ request('status') }}" placeholder="active, planning, blocked...">
                </div>
                <div class="col-md-3">
                    <button class="pm-btn pm-btn--primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Filtrar</button>
                    <a class="pm-btn" href="{{ route('project_manager.projects.index') }}"><i class="fa-solid fa-xmark"></i> Limpar</a>
                </div>
            </form>
        </div>

        <div class="pm-card">
            <table class="pm-table lsg-datatable">
                <thead><tr><th>Projeto</th><th>Status</th><th>Prioridade</th><th class="text-end">Operação</th></tr></thead>
                <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td>
                            <strong>{{ $project->name }}</strong>
                            <div class="pm-muted pm-small">{{ $project->slug ?? '-' }}</div>
                        </td>
                        <td><span class="pm-pill pm-pill--gold">{{ $project->status ?? 'active' }}</span></td>
                        <td>{{ $project->priority ?? '-' }}</td>
                        <td class="text-end">
                            <div class="pm-actions pm-actions--right">
                                <a class="pm-btn pm-btn--compact pm-btn--primary" href="{{ route('project_manager.projects.show', $project->id) }}"><i class="fa-solid fa-eye"></i> Abrir</a>
                                <a class="pm-btn pm-btn--compact pm-btn--warning" href="{{ route('project_manager.projects.edit', $project->id) }}"><i class="fa-solid fa-pencil"></i> Editar</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="pm-empty">Ainda não existem projetos.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection


