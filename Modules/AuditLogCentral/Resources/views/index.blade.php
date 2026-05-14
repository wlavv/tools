@extends(config('audit-log-central.layout'))

@section('content')
@include('audit-log-central::partials.styles')
<div class="audit-wrap container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h1 class="h3 mb-1">Audit Logs</h1><div class="audit-muted">Pesquisa e inspeção de eventos auditáveis.</div></div>
        <a href="{{ route('audit_log_central.dashboard') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Dashboard</a>
    </div>

    <div class="audit-layout">
        <form method="GET" class="audit-card audit-filter">
            <h5>Filtros</h5>
            <label>Módulo</label><select name="module"><option value="">Todos</option>@foreach($modules as $module)<option value="{{ $module }}" @selected(($filters['module'] ?? '') === $module)>{{ $module }}</option>@endforeach</select>
            <label class="mt-2">Severidade</label><select name="severity"><option value="">Todas</option>@foreach($severities as $severity)<option value="{{ $severity }}" @selected(($filters['severity'] ?? '') === $severity)>{{ $severity }}</option>@endforeach</select>
            <label class="mt-2">Status</label><input name="status" value="{{ $filters['status'] ?? '' }}" placeholder="success, failed...">
            <label class="mt-2">Evento</label><input name="event" value="{{ $filters['event'] ?? '' }}">
            <label class="mt-2">Utilizador</label><input name="user" value="{{ $filters['user'] ?? '' }}">
            <label class="mt-2">Entity Type</label><input name="entity_type" value="{{ $filters['entity_type'] ?? '' }}">
            <label class="mt-2">Entity ID</label><input name="entity_id" value="{{ $filters['entity_id'] ?? '' }}">
            <label class="mt-2">De</label><input type="date" name="from" value="{{ $filters['from'] ?? '' }}">
            <label class="mt-2">Até</label><input type="date" name="to" value="{{ $filters['to'] ?? '' }}">
            <button class="btn btn-outline-primary w-100 mt-3"><i class="fa-solid fa-filter"></i> Filtrar</button>
            <a href="{{ route('audit_log_central.index') }}" class="btn btn-outline-secondary w-100 mt-2">Limpar</a>
        </form>

        <div class="audit-card">
            <table class="audit-table">
                <thead><tr><th>Data</th><th>Severidade</th><th>Módulo</th><th>Evento</th><th>Entidade</th><th>User</th><th>Tags</th><th></th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ optional($log->occurred_at)->format('Y-m-d H:i:s') }}</td>
                        <td><span class="audit-badge {{ $log->severity }}">{{ $log->severity }}</span></td>
                        <td>{{ $log->module }}</td>
                        <td><strong>{{ $log->event }}</strong><br><small class="audit-muted">{{ $log->action }}</small></td>
                        <td><small>{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</small></td>
                        <td>{{ $log->user_name ?? $log->user_email ?? 'System' }}</td>
                        <td>@foreach($log->tags as $tag)<span class="badge bg-light text-dark border">{{ $tag->tag }}</span>@endforeach</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('audit_log_central.show', $log) }}"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="audit-muted text-center">Sem logs encontrados.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
