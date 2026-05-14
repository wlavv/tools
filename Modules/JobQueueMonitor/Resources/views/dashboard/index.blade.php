@extends('job-queue-monitor::layouts.module')

@section('content')
@include('job-queue-monitor::partials.styles')
<div class="jqm-wrap">
    <div class="prm-dashboard-grid jqm-kpi-grid">
        <div class="prm-dashboard-metric roles">
            <div><div class="prm-dashboard-metric__label">Execucoes 24h</div><div class="prm-dashboard-metric__value">{{ $total_24h }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-layer-group"></i></div>
        </div>
        <div class="prm-dashboard-metric users">
            <div><div class="prm-dashboard-metric__label">Sucesso 24h</div><div class="prm-dashboard-metric__value">{{ $success_24h }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="prm-dashboard-metric critical">
            <div><div class="prm-dashboard-metric__label">Falhas abertas</div><div class="prm-dashboard-metric__value">{{ $failed_open }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="prm-dashboard-metric permissions">
            <div><div class="prm-dashboard-metric__label">Em execucao</div><div class="prm-dashboard-metric__value">{{ $processing }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-spinner"></i></div>
        </div>
        <div class="prm-dashboard-metric roles">
            <div><div class="prm-dashboard-metric__label">Retrying</div><div class="prm-dashboard-metric__value">{{ $retrying }}</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-rotate-right"></i></div>
        </div>
        <div class="prm-dashboard-metric users">
            <div><div class="prm-dashboard-metric__label">Duracao media</div><div class="prm-dashboard-metric__value">{{ number_format($avg_duration_ms / 1000, 2) }}s</div></div>
            <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-stopwatch"></i></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card jqm-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fa-solid fa-list-check mr-2"></i>Ultimas execucoes</h5>
                    <a href="{{ route('job_queue_monitor.failed.index') }}" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-triangle-exclamation"></i> Falhas</a>
                </div>
                <div class="table-responsive">
                    <table class="table jqm-table table-hover mb-0">
                        <thead><tr><th>Status</th><th>Job</th><th>Queue</th><th>Tent.</th><th>Duracao</th><th>Data</th><th></th></tr></thead>
                        <tbody>
                        @forelse($latest_runs as $run)
                            <tr>
                                <td>@include('job-queue-monitor::partials.status', ['status' => $run->status])</td>
                                <td class="font-weight-bold">{{ class_basename($run->job_name) }}</td>
                                <td>{{ $run->queue ?: '-' }}</td>
                                <td>{{ $run->attempts }}</td>
                                <td>{{ $run->duration_ms ? number_format($run->duration_ms / 1000, 2) . 's' : '-' }}</td>
                                <td>{{ optional($run->created_at)->format('d/m/Y H:i:s') }}</td>
                                <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('job_queue_monitor.show', $run) }}"><i class="fa-solid fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-muted text-center py-4">Ainda nao existem execucoes monitorizadas.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div></div>
        </div>
        <div class="col-lg-4 mb-3">
            <div class="card jqm-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fa-solid fa-heart-pulse mr-2"></i>Saude</h5>
                    <form method="POST" action="{{ route('job_queue_monitor.health.run') }}">@csrf<button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-rotate"></i></button></form>
                </div>
                @foreach($health as $check)
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between"><strong>{{ $check->label }}</strong>@include('job-queue-monitor::partials.status', ['status' => $check->status])</div>
                        <div class="text-muted small mt-2">{{ $check->message }}</div>
                    </div>
                @endforeach
            </div></div>
        </div>
    </div>
</div>
@endsection
