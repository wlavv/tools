@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if(session('error'))<div class="wc-alert wc-alert-warning">{{ session('error') }}</div>@endif

@php($summary = $item->summary ?? [])
@php($topMatches = collect(data_get($item->metadata, 'top_matches', [])))
@php($expectedProductId = $groundTruth['expected_product_id'] ?? null)
@php($expectedRank = $accuracy['expected_rank'] ?? null)

<div class="wc-detail-hero wc-detail-hero-resource">
    <div>
        <span class="wc-eyebrow"><i class="fa-solid fa-flask-vial"></i> Benchmark Run</span>
        <h2>Run #{{ $item->id }}</h2>
        <p>{{ str_replace('_', ' ', $item->status) }}</p>
        <div class="wc-detail-tags">
            <span class="wc-badge">Session #{{ $item->id_session }}</span>
            <span class="wc-badge">{{ $item->store->name ?? $item->session?->store?->name ?? 'No store' }}</span>
            <span class="wc-badge">{{ $item->created_at?->format('Y-m-d H:i') }}</span>
            @if($groundTruth['scenario_label'] ?? null)<span class="wc-badge">{{ $groundTruth['scenario_label'] }}</span>@endif
            @if($expectedProductId)<span class="wc-badge">Expected #{{ $expectedProductId }}</span>@endif
        </div>
    </div>
    <div class="wc-detail-icon"><i class="fa-solid fa-flask-vial"></i></div>
</div>

<div class="wc-grid wc-kpi-grid">
    <div class="wc-kpi-card wc-kpi-card-store"><div class="wc-kpi-content"><h3>Flows</h3><div class="wc-kpi">{{ $summary['successful_flows'] ?? 0 }}/{{ $summary['flows_total'] ?? $item->results->count() }}</div><div class="wc-muted">Completed successfully</div></div><i class="fa-solid fa-code-branch wc-kpi-bg-icon"></i></div>
    <div class="wc-kpi-card wc-kpi-card-product"><div class="wc-kpi-content"><h3>Average</h3><div class="wc-kpi">{{ $summary['average_total_time_ms'] ?? '-' }}</div><div class="wc-muted">milliseconds per flow</div></div><i class="fa-solid fa-stopwatch wc-kpi-bg-icon"></i></div>
    <div class="wc-kpi-card wc-kpi-card-catalogue"><div class="wc-kpi-content"><h3>Fastest</h3><div class="wc-kpi">{{ $summary['fastest_flow'] ?? '-' }}</div><div class="wc-muted">Lowest total time</div></div><i class="fa-solid fa-bolt wc-kpi-bg-icon"></i></div>
    <div class="wc-kpi-card wc-kpi-card-resource"><div class="wc-kpi-content"><h3>Best confidence</h3><div class="wc-kpi">{{ $summary['highest_confidence_flow'] ?? '-' }}</div><div class="wc-muted">OpenCV normalization</div></div><i class="fa-solid fa-crosshairs wc-kpi-bg-icon"></i></div>
</div>

@if($expectedProductId)
    <div class="wc-card wc-spaced-card">
        <div class="wc-section-head">
            <div>
                <h3>Ground truth result</h3>
                <p class="wc-muted">Current WebCatalogue ranking for the expected product at benchmark time.</p>
            </div>
        </div>
        <div class="wc-rich-meta">
            <span class="wc-rich-metric"><i class="fa-solid fa-bullseye"></i>Expected product #{{ $expectedProductId }}</span>
            <span class="wc-rich-metric"><i class="fa-solid fa-ranking-star"></i>Rank {{ $expectedRank ?: 'missed top 5' }}</span>
            <span class="wc-rich-metric"><i class="fa-solid fa-circle-check"></i>Top 1 {{ (int) $expectedRank === 1 ? 'yes' : 'no' }}</span>
            <span class="wc-rich-metric"><i class="fa-solid fa-list-ol"></i>Top 3 {{ $expectedRank && (int) $expectedRank <= 3 ? 'yes' : 'no' }}</span>
        </div>
    </div>
@else
    <div class="wc-alert wc-alert-warning wc-spaced-card">
        This benchmark has no ground truth. Save the real product in the session before running the benchmark to calculate accuracy.
    </div>
@endif

<div class="wc-card wc-spaced-card">
    <div class="wc-section-head">
        <div>
            <h3>Flow comparison</h3>
            <p class="wc-muted">Legacy VPS, Rise-S base and Rise-S incremental are stored as separate rows.</p>
        </div>
        <div class="wc-actions-row">
            <a class="wc-secondary-btn" href="{{ route('webcatalogue.recognition.benchmarks.export_csv') }}"><i class="fa-solid fa-file-csv"></i> Export CSV</a>
            <a class="wc-secondary-btn" href="{{ route('webcatalogue.recognition.benchmarks.calls_csv') }}"><i class="fa-solid fa-network-wired"></i> Calls CSV</a>
        </div>
    </div>

    <div class="wc-table-wrap">
        <table class="wc-table">
            <thead>
                <tr>
                    <th>Flow</th>
                    <th>Stage</th>
                    <th>Status</th>
                    <th>Total ms</th>
                    <th>Quality</th>
                    <th>Confidence</th>
                    <th>Markers</th>
                    <th>Identifiers</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item->results as $result)
                    <tr>
                        <td><strong>{{ $result->flow_label ?: $result->flow_key }}</strong><br><span class="wc-muted">{{ $result->flow_key }}</span></td>
                        <td>{{ $result->flow_stage ?: '-' }}</td>
                        <td><span class="wc-badge wc-status-{{ $result->status }}">{{ str_replace('_', ' ', $result->status) }}</span></td>
                        <td>{{ $result->total_time_ms ?? '-' }}</td>
                        <td>{{ $result->quality_score !== null ? round((float) $result->quality_score, 2) : '-' }}</td>
                        <td>{{ $result->normalize_confidence !== null ? round((float) $result->normalize_confidence, 4) : '-' }}</td>
                        <td>{{ $result->marker_count ?? '-' }}</td>
                        <td>{{ $result->identifier_count ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="wc-card wc-spaced-card">
    <div class="wc-section-head">
        <div>
            <h3>Service calls</h3>
            <p class="wc-muted">HTTP timings per endpoint, flow and server response.</p>
        </div>
    </div>

    <div class="wc-table-wrap">
        <table class="wc-table">
            <thead>
                <tr>
                    <th>Flow</th>
                    <th>Endpoint</th>
                    <th>Status</th>
                    <th>HTTP</th>
                    <th>Client ms</th>
                    <th>Server ms</th>
                    <th>Gateway ms</th>
                    <th>Req KB</th>
                    <th>Resp KB</th>
                    <th>Served by</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item->results as $result)
                    @foreach($result->calls as $call)
                        @php($headers = $call->headers ?? [])
                        <tr>
                            <td>{{ $call->flow_key }}</td>
                            <td>{{ $call->endpoint_key }}</td>
                            <td><span class="wc-badge wc-status-{{ $call->status }}">{{ str_replace('_', ' ', $call->status) }}</span></td>
                            <td>{{ $call->http_status ?? '-' }}</td>
                            <td>{{ $call->client_time_ms ?? '-' }}</td>
                            <td>{{ $call->server_time_ms ?? '-' }}</td>
                            <td>{{ $call->gateway_time_ms ?? '-' }}</td>
                            <td>{{ $call->request_bytes ? round($call->request_bytes / 1024, 1) : '-' }}</td>
                            <td>{{ $call->response_bytes ? round($call->response_bytes / 1024, 1) : '-' }}</td>
                            <td>{{ $headers['x-served-by'] ?? $headers['server'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="wc-card wc-spaced-card">
    <div class="wc-section-head">
        <div>
            <h3>Endpoint comparison</h3>
            <p class="wc-muted">Side-by-side latency matrix for legacy, Rise-S base and incremental flows.</p>
        </div>
    </div>

    <div class="wc-table-wrap">
        <table class="wc-table">
            <thead>
                <tr>
                    <th>Endpoint</th>
                    @foreach($callComparison['flows'] as $flow)
                        <th>{{ $flow }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($callComparison['rows'] as $row)
                    <tr>
                        <td><strong>{{ $row['endpoint'] }}</strong></td>
                        @foreach($callComparison['flows'] as $flow)
                            @php($cell = $row['flows'][$flow] ?? null)
                            <td>
                                @if($cell)
                                    <div><strong>{{ $cell['client_time_ms'] ?? '-' }} ms</strong> <span class="wc-badge wc-status-{{ $cell['status'] }}">{{ $cell['http_status'] ?? '-' }}</span></div>
                                    <div class="wc-muted">
                                        server {{ $cell['server_time_ms'] ?? '-' }} ms
                                        @if($cell['gateway_time_ms'] !== null) - gateway {{ $cell['gateway_time_ms'] }} ms @endif
                                    </div>
                                    <div class="wc-muted">req {{ $cell['request_kb'] ?? '-' }} KB - resp {{ $cell['response_kb'] ?? '-' }} KB</div>
                                @else
                                    <span class="wc-muted">-</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + count($callComparison['flows']) }}">
                            <div class="wc-list-empty"><i class="fa-solid fa-network-wired"></i><div><strong>No service call metrics yet.</strong><br><span>Run a new benchmark after the calls table migration.</span></div></div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="wc-grid wc-spaced-card">
    @foreach($item->results as $result)
        @php($metrics = $result->metrics ?? [])
        <div class="wc-card">
            <div class="wc-section-head">
                <div>
                    <h3>{{ $result->flow_label ?: $result->flow_key }}</h3>
                    <p class="wc-muted">{{ $result->flow_stage ?: 'no stage' }} - {{ $result->base_url }}</p>
                </div>
                <span class="wc-badge wc-status-{{ $result->status }}">{{ str_replace('_', ' ', $result->status) }}</span>
            </div>

            @if($result->error)
                <div class="wc-alert wc-alert-warning">{{ $result->error }}</div>
            @endif

            <div class="wc-capture-analysis">
                <div>
                    <div class="wc-capture-shot">
                        @if($result->normalized_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($result->normalized_path) }}" alt="Normalized benchmark image">
                        @else
                            <i class="fa-solid fa-image"></i>
                        @endif
                    </div>
                    <p class="wc-capture-label">Normalized</p>
                </div>
                <div>
                    <div class="wc-capture-shot">
                        @if($result->debug_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($result->debug_path) }}" alt="Debug benchmark image">
                        @else
                            <i class="fa-solid fa-crop-simple"></i>
                        @endif
                    </div>
                    <p class="wc-capture-label">Debug</p>
                </div>
            </div>

            <div class="wc-score-breakdown">
                <span><strong>Total</strong>{{ $result->total_time_ms ?? '-' }} ms</span>
                <span><strong>Quality</strong>{{ $result->quality_time_ms ?? '-' }} ms</span>
                <span><strong>Normalize</strong>{{ $result->normalize_time_ms ?? '-' }} ms</span>
                <span><strong>Markers</strong>{{ $result->markers_time_ms ?? '-' }} ms</span>
                <span><strong>Identifiers</strong>{{ $result->identifiers_time_ms ?? '-' }} ms</span>
                <span><strong>Profile</strong>{{ $metrics['normalize_profile'] ?? '-' }}</span>
                <span><strong>Frame crop</strong>{{ array_key_exists('framing_crop_applied', $metrics) ? ($metrics['framing_crop_applied'] ? 'yes' : 'no') : '-' }}</span>
                <span><strong>Frame area</strong>{{ isset($metrics['framing_area_ratio']) ? round((float) $metrics['framing_area_ratio'], 4) : '-' }}</span>
            </div>
            @if(!empty($metrics['framing_margins']) && is_array($metrics['framing_margins']))
                <p class="wc-capture-meta">
                    Margins:
                    L {{ round((float) ($metrics['framing_margins']['left'] ?? 0), 4) }},
                    R {{ round((float) ($metrics['framing_margins']['right'] ?? 0), 4) }},
                    T {{ round((float) ($metrics['framing_margins']['top'] ?? 0), 4) }},
                    B {{ round((float) ($metrics['framing_margins']['bottom'] ?? 0), 4) }}
                </p>
            @endif
        </div>
    @endforeach
</div>
</div>
@endsection
