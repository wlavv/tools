@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
@php
    $runCost = $run->providerResponses->sum(fn ($response) => (float) ($response->cost_estimate ?? 0));
@endphp
<div>
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('ai_consensus.logs.index', ['run_id' => $run->id]) }}" class="btn btn-outline-secondary">
            <i class="fas fa-list"></i> Logs
        </a>
        <a href="{{ route('ai_consensus.runs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Status</div>
                @include('ai-consensus::partials.status-badge', ['status' => $run->status])
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Source</div>
                <strong>{{ $run->source_module }}</strong> / {{ $run->source_type }}
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Template</div>
                {{ $run->template?->template_key ?? '-' }}
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card"><div class="card-body">
                <div class="text-muted small">Output</div>
                {{ $run->output_type }} · {{ $run->final_score ?? '-' }} · ${{ number_format($runCost, 4) }}
            </div></div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Final Output</div>
        <div class="card-body">
            <pre class="mb-0" style="white-space: pre-wrap;">{{ $run->final_output ?: $run->error_message ?: 'Sem output ainda.' }}</pre>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Provider Responses</div>
                <div class="card-body table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Provider</th><th>Status</th><th>Score</th><th>Cost</th><th>Latency</th></tr></thead>
                        <tbody>
                            @foreach($run->providerResponses as $response)
                                <tr>
                                    <td>{{ $response->provider?->provider_key ?? 'internal' }}</td>
                                    <td>@include('ai-consensus::partials.status-badge', ['status' => $response->status])</td>
                                    <td>{{ $response->score ?? '-' }}</td>
                                    <td>${{ number_format((float) ($response->cost_estimate ?? 0), 4) }}</td>
                                    <td>{{ $response->latency_ms ?? '-' }} ms</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">Messages</div>
                <div class="card-body">
                    @foreach($run->messages as $message)
                        <div class="border-bottom pb-2 mb-2">
                            <strong>{{ $message->role }}</strong>
                            <div class="small text-muted">{{ optional($message->created_at)->format('Y-m-d H:i') }}</div>
                            <div>{{ \Illuminate\Support\Str::limit($message->message, 350) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
