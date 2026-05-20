@extends(config('audit-log-central.layout'))

@section('content')
@include('audit-log-central::partials.styles')
<div class="audit-wrap lsg-content py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h1 class="h3 mb-1">Entity Timeline</h1><div class="audit-muted">{{ $entityType }} #{{ $entityId }}</div></div>
        <a href="{{ route('audit_log_central.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Logs</a>
    </div>

    <div class="audit-card">
        @forelse($logs as $log)
            <div class="audit-timeline-item">
                <div class="d-flex justify-content-between">
                    <strong>{{ $log->event }}</strong>
                    <span class="audit-badge {{ $log->severity }}">{{ $log->severity }}</span>
                </div>
                <div class="audit-muted">{{ optional($log->occurred_at)->format('Y-m-d H:i:s') }} · {{ $log->module }} · {{ $log->user_name ?? 'System' }}</div>
                @foreach($log->changes as $change)
                    <div><small><strong>{{ $change->field }}</strong>: {{ data_get($change->old_value, 'value') }} → {{ data_get($change->new_value, 'value') }}</small></div>
                @endforeach
                <a href="{{ route('audit_log_central.show', $log) }}" class="btn btn-sm btn-outline-primary mt-2"><i class="fa-solid fa-eye"></i> Detalhe</a>
            </div>
        @empty
            <div class="audit-muted">Sem eventos para esta entidade.</div>
        @endforelse
        {{ $logs->links() }}
    </div>
</div>
@endsection
