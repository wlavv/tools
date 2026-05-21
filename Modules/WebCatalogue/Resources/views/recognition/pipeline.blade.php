@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
    @if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

    <div class="wc-hero-card">
        <div>
            <div class="wc-eyebrow"><i class="fa-solid fa-chart-line"></i> Recognition Pipeline v2</div>
            <h2>Operational recognition metrics</h2>
            <p>Quality gate, weighted scoring, latency and ground truth performance for visual product recognition.</p>
        </div>
        <div class="wc-hero-actions">
            <a class="wc-secondary-btn" href="{{ route('webcatalogue.recognition.index') }}"><i class="fa-solid fa-arrow-left"></i> Recognition</a>
            <a class="wc-primary-btn" href="{{ route('webcatalogue.recognition.pipeline.summary') }}"><i class="fa-solid fa-code"></i> JSON</a>
            <form method="POST" action="{{ route('webcatalogue.recognition.pipeline.flush') }}" onsubmit="return confirm('Clear all recognition sessions, captures, matches and pipeline metrics?');">
                @csrf
                <button type="submit" class="wc-secondary-btn"><i class="fa-solid fa-trash-can"></i> Flush stats</button>
            </form>
        </div>
    </div>

    <div class="wc-grid wc-kpi-grid">
        <div class="wc-kpi-card wc-kpi-card-store"><div class="wc-kpi-content"><h3>Total scans</h3><div class="wc-kpi">{{ $totalScans }}</div><div class="wc-muted">{{ $acceptedScans }} accepted · {{ $ambiguousScans }} ambiguous</div></div><i class="fa-solid fa-camera wc-kpi-bg-icon"></i></div>
        <div class="wc-kpi-card wc-kpi-card-product"><div class="wc-kpi-content"><h3>Acceptance</h3><div class="wc-kpi">{{ $acceptanceRate }}%</div><div class="wc-muted">{{ $rejectionRate }}% rejected</div></div><i class="fa-solid fa-circle-check wc-kpi-bg-icon"></i></div>
        <div class="wc-kpi-card wc-kpi-card-catalogue"><div class="wc-kpi-content"><h3>Avg latency</h3><div class="wc-kpi">{{ $averageResponseTime ?: 0 }}ms</div><div class="wc-muted">p95 {{ $p95ResponseTime ?? '—' }}ms · p99 {{ $p99ResponseTime ?? '—' }}ms</div></div><i class="fa-solid fa-stopwatch wc-kpi-bg-icon"></i></div>
        <div class="wc-kpi-card wc-kpi-card-resource"><div class="wc-kpi-content"><h3>Quality</h3><div class="wc-kpi">{{ $averageQualityScore ?: 0 }}</div><div class="wc-muted">Avg final score {{ $averageFinalScore ?: 0 }}</div></div><i class="fa-solid fa-gauge-high wc-kpi-bg-icon"></i></div>
    </div>

    <div class="wc-grid wc-spaced-card">
        <div class="wc-card">
            <div class="wc-section-head"><div><h3>Latency breakdown</h3><p class="wc-muted">Average processing time by expensive stage.</p></div></div>
            <div class="wc-rich-meta">
                <span class="wc-rich-metric"><i class="fa-solid fa-crop-simple"></i>Prepare {{ $averageInputPreparationTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-vector-square"></i>Perspective {{ $averagePerspectiveTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-hashtag"></i>Hash gen {{ $averageHashGenerationTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-magnifying-glass-chart"></i>Hash search {{ $averageHashSearchTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-font"></i>OCR {{ $averageOcrTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-fingerprint"></i>ORB {{ $averageOrbTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-scale-balanced"></i>Scoring {{ $averageScoringTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-database"></i>DB {{ $averageDatabaseTime ?: 0 }}ms</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-chart-simple"></i>Median {{ $medianResponseTime ?? '—' }}ms</span>
            </div>
        </div>

        <div class="wc-card">
            <div class="wc-section-head"><div><h3>Ground truth</h3><p class="wc-muted">Accuracy is calculated only when expected product data exists.</p></div></div>
            <div class="wc-rich-meta">
                <span class="wc-rich-metric"><i class="fa-solid fa-vial"></i>{{ $groundTruthCount }} tests</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-bullseye"></i>Top-1 {{ $top1Accuracy === null ? '—' : $top1Accuracy.'%' }}</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-layer-group"></i>Top-3 {{ $top3Accuracy === null ? '—' : $top3Accuracy.'%' }}</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-triangle-exclamation"></i>FP {{ $falsePositiveRate === null ? '—' : $falsePositiveRate.'%' }}</span>
                <span class="wc-rich-metric"><i class="fa-solid fa-circle-minus"></i>FN {{ $falseNegativeRate === null ? '—' : $falseNegativeRate.'%' }}</span>
            </div>
        </div>
    </div>

    <div class="wc-editor-layout" style="margin-top:16px">
        <div class="wc-card">
            <div class="wc-section-head"><div><h3>Recent scans</h3><p class="wc-muted">Last pipeline executions with score, quality and latency.</p></div></div>
            <div class="wc-table-wrap">
                <table class="wc-table">
                    <thead><tr><th>Scan</th><th>Status</th><th>Product</th><th>Quality</th><th>Score</th><th>Latency</th><th>Reason</th></tr></thead>
                    <tbody>
                    @forelse($recentScans as $scan)
                        <tr>
                            <td><code>{{ \Illuminate\Support\Str::limit($scan->scan_uuid, 8, '') }}</code></td>
                            <td><span class="wc-badge wc-status-{{ $scan->status }}">{{ $scan->status }}</span></td>
                            <td>{{ $scan->topProduct?->name ?? '—' }}</td>
                            <td>{{ $scan->quality_score === null ? '—' : round($scan->quality_score, 1) }}</td>
                            <td>{{ $scan->score_final === null ? '—' : round($scan->score_final, 1) }}</td>
                            <td>{{ $scan->timings?->total_processing_time_ms ?? '—' }}ms</td>
                            <td>{{ $scan->decision_reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="wc-list-empty"><i class="fa-solid fa-chart-line"></i><div><strong>No pipeline scans yet.</strong></div></div></td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="wc-card">
            <div class="wc-section-head"><div><h3>Recognition profiles</h3><p class="wc-muted">Scans grouped by operational profile.</p></div></div>
            <div class="wc-ops-list">
                @foreach($scansByProfile as $row)
                    <div class="wc-ops-row"><div><strong>{{ $row->recognition_profile }}</strong><span>{{ $row->total }} scans</span></div></div>
                @endforeach
            </div>
            <div class="wc-section-head" style="margin-top:16px"><div><h3>Scopes</h3></div></div>
            <div class="wc-ops-list">
                @foreach($scansByScope as $row)
                    <div class="wc-ops-row"><div><strong>{{ $row->product_scope }}</strong><span>{{ $row->total }} scans</span></div></div>
                @endforeach
            </div>
        </aside>
    </div>
</div>
@endsection
