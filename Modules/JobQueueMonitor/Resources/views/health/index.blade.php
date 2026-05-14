@extends('job-queue-monitor::layouts.module')

@section('content')
@include('job-queue-monitor::partials.styles')
<div class="jqm-wrap">
    <div class="card jqm-card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Saúde das queues</h5>
            <form method="POST" action="{{ route('job_queue_monitor.health.run') }}">@csrf<button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-rotate"></i> Executar</button></form>
        </div>
        @foreach($health as $check)
            <div class="p-3 border rounded mb-3">
                <div class="d-flex justify-content-between"><strong>{{ $check->label }}</strong>@include('job-queue-monitor::partials.status', ['status' => $check->status])</div>
                <div class="text-muted small mt-2">{{ $check->message }}</div>
                <div class="text-muted small">Última verificação: {{ optional($check->checked_at)->format('d/m/Y H:i:s') }}</div>
            </div>
        @endforeach
    </div></div>
</div>
@endsection
