@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if(session('error'))<div class="wc-alert wc-alert-warning">{{ session('error') }}</div>@endif

<div class="wc-hero-card">
    <div>
        <div class="wc-eyebrow"><i class="fa-solid fa-flask-vial"></i> Academic Benchmark</div>
        <h2>Recognition benchmark flows</h2>
        <p>Controlled comparison between legacy VPS, Rise-S base and the incremental Rise-S pipeline.</p>
    </div>
    <div class="wc-hero-actions">
        <a class="wc-secondary-btn" href="{{ route('webcatalogue.recognition.benchmarks.export_csv') }}"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
        <a class="wc-primary-btn" href="{{ route('webcatalogue.recognition.sessions.index') }}"><i class="fa-solid fa-camera"></i> Sessions</a>
    </div>
</div>

<div class="wc-grid wc-spaced-card">
    @forelse($flowSummary as $flow)
        @php
            $avgMs = $flow->avg_total_time_ms ? round((float) $flow->avg_total_time_ms, 0) : null;
            $quality = $flow->avg_quality_score ? round((float) $flow->avg_quality_score, 2) : null;
            $confidence = $flow->avg_normalize_confidence ? round((float) $flow->avg_normalize_confidence, 4) : null;
            $qualityWidth = $quality !== null ? min(100, max(0, $quality)) : 0;
            $confidenceWidth = $confidence !== null ? min(100, max(0, $confidence * 100)) : 0;
        @endphp
        <div class="wc-card">
            <div class="wc-section-head">
                <div>
                    <h3>{{ $flow->flow_label ?: $flow->flow_key }}</h3>
                    <p class="wc-muted">{{ $flow->flow_stage ?: 'no stage' }} - {{ $flow->ok_runs }}/{{ $flow->total_runs }} ok</p>
                </div>
            </div>
            <div class="wc-score-bars">
                <div class="wc-score-row"><span>Avg latency</span><div class="wc-score-track"><div style="width:{{ $avgMs ? min(100, max(8, 100 - ($avgMs / 100))) : 0 }}%"></div></div><strong>{{ $avgMs ?? '-' }} ms</strong></div>
                <div class="wc-score-row"><span>Quality</span><div class="wc-score-track"><div style="width:{{ $qualityWidth }}%"></div></div><strong>{{ $quality ?? '-' }}</strong></div>
                <div class="wc-score-row"><span>Confidence</span><div class="wc-score-track"><div style="width:{{ $confidenceWidth }}%"></div></div><strong>{{ $confidence ?? '-' }}</strong></div>
            </div>
        </div>
    @empty
        <div class="wc-card">
            <div class="wc-list-empty"><i class="fa-solid fa-chart-simple"></i><div><strong>No flow metrics yet.</strong><br><span>Run benchmarks to build the comparative graph.</span></div></div>
        </div>
    @endforelse
</div>

<div class="wc-card wc-spaced-card">
    <div class="wc-section-head">
        <div>
            <h3>Benchmark runs</h3>
            <p class="wc-muted">Each run stores the same scan tested against the configured flows.</p>
        </div>
    </div>

    <div class="wc-table-wrap">
        <table class="wc-table">
            <thead>
                <tr>
                    <th>Run</th>
                    <th>Session</th>
                    <th>Store</th>
                    <th>Status</th>
                    <th>Flows</th>
                    <th>Fastest</th>
                    <th>Avg ms</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $run)
                    @php($summary = $run->summary ?? [])
                    <tr>
                        <td><a href="{{ route('webcatalogue.recognition.benchmarks.show', $run) }}">#{{ $run->id }}</a></td>
                        <td>
                            @if($run->session)
                                <a href="{{ route('webcatalogue.recognition.sessions.show', $run->session) }}">Session #{{ $run->id_session }}</a>
                            @else
                                #{{ $run->id_session }}
                            @endif
                        </td>
                        <td>{{ $run->store->name ?? $run->session?->store?->name ?? '-' }}</td>
                        <td><span class="wc-badge wc-status-{{ $run->status }}">{{ str_replace('_', ' ', $run->status) }}</span></td>
                        <td>{{ $summary['successful_flows'] ?? 0 }} / {{ $summary['flows_total'] ?? $run->results->count() }}</td>
                        <td>{{ $summary['fastest_flow'] ?? '-' }}</td>
                        <td>{{ $summary['average_total_time_ms'] ?? '-' }}</td>
                        <td>{{ $run->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="wc-list-empty"><i class="fa-solid fa-flask"></i><div><strong>No benchmark runs yet.</strong><br><span>Open a recognition session and run the benchmark.</span></div></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $items->links() }}
</div>
</div>
@endsection
