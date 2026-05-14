@extends(config('audit-log-central.layout'))

@section('content')
@include('audit-log-central::partials.styles')
<div class="audit-wrap">
    <div class="prm-dashboard-grid">
        <div class="prm-dashboard-metric roles">
            <div><div class="prm-dashboard-metric__label">Total</div><div class="prm-dashboard-metric__value">{{ number_format($totals['total']) }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-database"></i></div>
        </div>
        <div class="prm-dashboard-metric users">
            <div><div class="prm-dashboard-metric__label">Hoje</div><div class="prm-dashboard-metric__value">{{ number_format($totals['today']) }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-calendar-day"></i></div>
        </div>
        <div class="prm-dashboard-metric critical">
            <div><div class="prm-dashboard-metric__label">Risco</div><div class="prm-dashboard-metric__value">{{ number_format($totals['warnings']) }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="prm-dashboard-metric permissions">
            <div><div class="prm-dashboard-metric__label">Seguranca</div><div class="prm-dashboard-metric__value">{{ number_format($totals['security']) }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-shield-halved"></i></div>
        </div>
    </div>

    <div class="audit-layout mt-3">
        <div class="audit-card">
            <h5>Modulos mais ativos</h5>
            @forelse($byModule as $row)
                <div class="d-flex justify-content-between border-bottom py-2"><strong>{{ $row->module }}</strong><span>{{ $row->total }}</span></div>
            @empty
                <div class="audit-muted">Sem dados.</div>
            @endforelse
        </div>
        <div class="audit-card">
            <h5>Eventos recentes</h5>
            <table class="audit-table">
                <thead><tr><th>Data</th><th>Severidade</th><th>Modulo</th><th>Evento</th><th>User</th><th></th></tr></thead>
                <tbody>
                @foreach($recent as $log)
                    <tr>
                        <td>{{ optional($log->occurred_at)->format('Y-m-d H:i') }}</td>
                        <td><span class="audit-badge {{ $log->severity }}">{{ $log->severity }}</span></td>
                        <td>{{ $log->module }}</td>
                        <td>{{ $log->event }}</td>
                        <td>{{ $log->user_name ?? $log->user_email ?? 'System' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('audit_log_central.show', $log) }}"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
