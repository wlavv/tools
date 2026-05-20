@extends(config('audit-log-central.layout'))

@section('content')
@include('audit-log-central::partials.styles')
<div class="audit-wrap lsg-content py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h1 class="h3 mb-1">Audit Detail #{{ $auditLog->id }}</h1><div class="audit-muted">{{ $auditLog->uuid }}</div></div>
        <a href="{{ route('audit_log_central.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Voltar</a>
    </div>

    <div class="audit-grid audit-grid-4">
        <div class="audit-card"><div class="audit-muted">Módulo</div><strong>{{ $auditLog->module }}</strong></div>
        <div class="audit-card"><div class="audit-muted">Evento</div><strong>{{ $auditLog->event }}</strong></div>
        <div class="audit-card"><div class="audit-muted">Severidade</div><span class="audit-badge {{ $auditLog->severity }}">{{ $auditLog->severity }}</span></div>
        <div class="audit-card"><div class="audit-muted">Data</div><strong>{{ optional($auditLog->occurred_at)->format('Y-m-d H:i:s') }}</strong></div>
    </div>

    <div class="audit-card">
        <h5>Alterações</h5>
        @forelse($auditLog->changes as $change)
            <div class="mb-3">
                <strong>{{ $change->field }}</strong>
                <div class="audit-diff mt-2">
                    <pre class="audit-json audit-old">{{ json_encode($change->old_value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                    <pre class="audit-json audit-new">{{ json_encode($change->new_value, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @empty
            <div class="audit-muted">Sem alterações de campos.</div>
        @endforelse
    </div>

    <div class="audit-layout">
        <div class="audit-card">
            <h5>Contexto</h5>
            <div><strong>User:</strong> {{ $auditLog->user_name ?? 'System' }} {{ $auditLog->user_email ? '(' . $auditLog->user_email . ')' : '' }}</div>
            <div><strong>IP:</strong> {{ $auditLog->ip_address }}</div>
            <div><strong>Origem:</strong> {{ $auditLog->source }}</div>
            <div><strong>Entidade:</strong> {{ $auditLog->auditable_type }} #{{ $auditLog->auditable_id }}</div>
            <hr>
            <h6>Tags</h6>
            @foreach($auditLog->tags as $tag)<span class="badge bg-light text-dark border">{{ $tag->tag }}</span>@endforeach
            <hr>
            <h6>Relações</h6>
            @foreach($auditLog->relations as $relation)<div>{{ $relation->related_type }} #{{ $relation->related_id }} <small>{{ $relation->label }}</small></div>@endforeach
        </div>
        <div class="audit-card">
            <h5>Payload</h5>
            <pre class="audit-json">{{ json_encode($auditLog->payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
            <h5 class="mt-3">Metadata</h5>
            <pre class="audit-json">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
</div>
@endsection
