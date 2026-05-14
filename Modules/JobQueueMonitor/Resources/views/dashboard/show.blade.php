@extends('job-queue-monitor::layouts.module')

@section('content')
@include('job-queue-monitor::partials.styles')
<div class="container-fluid jqm-wrap">
    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card jqm-card"><div class="card-body">
                <h5 class="mb-3">Execução #{{ $run->id }}</h5>
                <p><strong>Status:</strong> @include('job-queue-monitor::partials.status', ['status' => $run->status])</p>
                <p><strong>Job:</strong><br>{{ $run->job_name }}</p>
                <p><strong>Queue:</strong> {{ $run->queue ?: '-' }}</p>
                <p><strong>Connection:</strong> {{ $run->connection ?: '-' }}</p>
                <p><strong>Tentativas:</strong> {{ $run->attempts }}</p>
                <p><strong>Duração:</strong> {{ $run->duration_ms ? number_format($run->duration_ms / 1000, 2) . 's' : '-' }}</p>
                <p><strong>Início:</strong> {{ optional($run->started_at)->format('d/m/Y H:i:s') ?: '-' }}</p>
                <p><strong>Fim:</strong> {{ optional($run->finished_at)->format('d/m/Y H:i:s') ?: '-' }}</p>
                @if($run->status === 'failed' && !$run->resolved_at)
                    <form method="POST" action="{{ route('job_queue_monitor.resolve', $run) }}" class="mt-3">
                        @csrf
                        <textarea name="resolution_note" class="form-control mb-2" rows="3" placeholder="Nota de resolução"></textarea>
                        <button class="btn btn-outline-success btn-sm"><i class="fa-solid fa-check"></i> Marcar resolvido</button>
                    </form>
                @endif
            </div></div>
        </div>
        <div class="col-lg-8 mb-3">
            <div class="card jqm-card mb-3"><div class="card-body">
                <h5>Erro</h5>
                @if($run->exception_message)
                    <div class="alert alert-danger">{{ $run->exception_message }}</div>
                    <div class="text-muted small">{{ $run->exception_file }}:{{ $run->exception_line }}</div>
                @else
                    <div class="text-muted">Sem erro registado.</div>
                @endif
            </div></div>
            <div class="card jqm-card mb-3"><div class="card-body">
                <h5>Payload</h5>
                <pre class="jqm-pre">{{ $run->payload ?: '-' }}</pre>
            </div></div>
            <div class="card jqm-card"><div class="card-body">
                <h5>Stack trace</h5>
                <pre class="jqm-pre">{{ $run->exception_trace ?: '-' }}</pre>
            </div></div>
        </div>
    </div>
</div>
@endsection
