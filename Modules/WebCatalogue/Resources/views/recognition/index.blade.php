@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-hero-card">
    <div>
        <div class="wc-eyebrow"><i class="fa-solid fa-camera"></i> Visual Recognition</div>
        <h2>Camera-based product discovery</h2>
        <p>Capture product recognition sessions, unmatched product requests and brand prospect demand generated from the public catalogue.</p>
    </div>
    <div class="wc-hero-actions">
        <a class="wc-secondary-btn" href="{{ route('webcatalogue.recognition.pipeline.index') }}"><i class="fa-solid fa-chart-line"></i> Pipeline v2</a>
        <a class="wc-primary-btn" href="{{ route('webcatalogue.recognition.sessions.index') }}"><i class="fa-solid fa-list"></i> Sessions</a>
        <a class="wc-secondary-btn" href="{{ route('webcatalogue.recognition.leads.index') }}"><i class="fa-solid fa-bullseye"></i> Leads</a>
    </div>
</div>

<div class="wc-grid wc-kpi-grid">
    <a class="wc-kpi-card wc-kpi-card-store wc-kpi-link" href="{{ route('webcatalogue.recognition.sessions.index', ['group' => 'all']) }}"><div class="wc-kpi-content"><h3>Total scans</h3><div class="wc-kpi">{{ $sessionsCount }}</div><div class="wc-muted">All recognition sessions</div></div><i class="fa-solid fa-camera wc-kpi-bg-icon"></i></a>
    <a class="wc-kpi-card wc-kpi-card-catalogue wc-kpi-link" href="{{ route('webcatalogue.recognition.sessions.index', ['group' => 'review']) }}"><div class="wc-kpi-content"><h3>Needs action</h3><div class="wc-kpi">{{ $actionNeededSessionsCount }}</div><div class="wc-muted">Open review queue</div></div><i class="fa-solid fa-list-check wc-kpi-bg-icon"></i></a>
    <a class="wc-kpi-card wc-kpi-card-product wc-kpi-link" href="{{ route('webcatalogue.recognition.sessions.index', ['group' => 'matched']) }}"><div class="wc-kpi-content"><h3>Match rate</h3><div class="wc-kpi">{{ $matchRate }}%</div><div class="wc-muted">Auto + manual matches</div></div><i class="fa-solid fa-circle-check wc-kpi-bg-icon"></i></a>
    <div class="wc-kpi-card wc-kpi-card-resource"><div class="wc-kpi-content"><h3>Dataset</h3><div class="wc-kpi">{{ $datasetCoveragePercent }}%</div><div class="wc-muted">{{ $fingerprintsCount }} / {{ $candidateImagesCount }} fingerprints</div></div><i class="fa-solid fa-fingerprint wc-kpi-bg-icon"></i></div>
</div>

<div class="wc-grid wc-spaced-card">
    <div class="wc-card">
        <div class="wc-section-head"><div><h3>Recognition health</h3><p class="wc-muted">Score distribution and session state.</p></div></div>
        <div class="wc-score-bars">
            @foreach($scoreBands as $label => $count)
                @php($width = $sessionsCount > 0 ? min(100, round(($count / $sessionsCount) * 100)) : 0)
                <div class="wc-score-row"><span>{{ $label }}</span><div class="wc-score-track"><div style="width:{{ $width }}%"></div></div><strong>{{ $count }}</strong></div>
            @endforeach
        </div>
        <div class="wc-rich-meta">
            <span class="wc-rich-metric"><i class="fa-solid fa-gauge-high"></i>Avg. {{ $averageScore ?: 0 }}</span>
            <a class="wc-rich-metric" href="{{ route('webcatalogue.recognition.sessions.index', ['group' => 'all']) }}"><i class="fa-solid fa-calendar-day"></i>{{ $todaySessionsCount }} today</a>
            <a class="wc-rich-metric" href="{{ route('webcatalogue.recognition.leads.index') }}"><i class="fa-solid fa-lightbulb"></i>{{ $newLeadsCount }} new leads</a>
        </div>
        <div class="wc-status-cloud">
            @foreach($statusCounts as $row)
                <a class="wc-badge wc-status-{{ $row['status'] }}" href="{{ route('webcatalogue.recognition.sessions.index', ['group' => $row['group']]) }}">{{ $row['status'] }}: {{ $row['total'] }}</a>
            @endforeach
        </div>
    </div>

    <div class="wc-card">
        <div class="wc-section-head"><div><h3>Dataset by store</h3><p class="wc-muted">Fingerprint coverage and last rebuild.</p></div></div>
        <div class="wc-ops-list">
            @forelse($storeDatasetRows as $store)
                @php($coverage = $store->candidate_images_count > 0 ? min(100, round(($store->fingerprinted_images_count / $store->candidate_images_count) * 100)) : 0)
                <a class="wc-ops-row" href="{{ route('webcatalogue.stores.show', $store) }}">
                    <div><strong>{{ $store->name }}</strong><span>{{ $store->latestFingerprintRebuildLog?->finished_at?->diffForHumans() ?? 'never rebuilt' }}</span></div>
                    <div class="wc-ops-meter"><span>{{ $coverage }}%</span><div class="wc-score-track"><div style="width:{{ $coverage }}%"></div></div></div>
                </a>
            @empty
                <div class="wc-list-empty"><i class="fa-solid fa-store"></i><div><strong>No stores yet.</strong></div></div>
            @endforelse
        </div>
    </div>

    <div class="wc-card">
        <div class="wc-section-head"><div><h3>Fingerprint rebuilds</h3><p class="wc-muted">Recent automated and manual executions.</p></div></div>
        <div class="wc-ops-list">
            @forelse($recentRebuildLogs as $log)
                <div class="wc-ops-row">
                    <div><strong>{{ $log->store->name ?? 'Store #'.$log->id_store }}</strong><span>{{ $log->trigger }} · {{ $log->finished_at?->format('Y-m-d H:i') ?: $log->created_at?->format('Y-m-d H:i') }}</span></div>
                    <span class="wc-badge wc-status-{{ $log->status }}">{{ $log->status }} · {{ $log->processed }} imgs</span>
                </div>
            @empty
                <div class="wc-list-empty"><i class="fa-solid fa-fingerprint"></i><div><strong>No rebuild history yet.</strong></div></div>
            @endforelse
        </div>
    </div>
</div>

<div class="wc-editor-layout" style="margin-top:16px">
    <div class="wc-card">
        <div class="wc-section-head"><div><h3>Recent sessions</h3><p class="wc-muted">Recent scans that still need action.</p></div></div>
        <div class="wc-rich-list">
            @forelse($recentSessions as $session)
                @php($capture = $session->captures->firstWhere('capture_type', 'object_photo') ?: $session->captures->first())
                <div class="wc-rich-card">
                    <div class="wc-rich-media wc-session-capture-media">
                        @if($capture?->resolved_url)
                            <img src="{{ $capture->resolved_url }}" alt="Session capture">
                        @else
                            <i class="fa-solid fa-camera"></i>
                        @endif
                    </div>
                    <div class="wc-rich-body"><div class="wc-rich-title"><h4><a href="{{ route('webcatalogue.recognition.sessions.show', $session) }}">Session #{{ $session->id }}</a></h4><span class="wc-badge">{{ $session->status }}</span></div><div class="wc-rich-meta"><span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $session->store->name ?? '—' }}</span><span class="wc-rich-metric"><i class="fa-solid fa-clock"></i>{{ $session->created_at?->format('Y-m-d H:i') }}</span></div></div>
                </div>
            @empty
                <div class="wc-list-empty"><i class="fa-solid fa-camera"></i><div><strong>No sessions need action.</strong><br><span>Matched sessions are available from the sessions archive.</span></div></div>
            @endforelse
        </div>
    </div>

    <aside class="wc-card">
        <div class="wc-section-head"><div><h3>Recent product requests</h3><p class="wc-muted">Unmatched product leads.</p></div></div>
        <div class="wc-rich-list">
            @forelse($recentLeads as $lead)
                <div class="wc-rich-card">
                    <div class="wc-rich-media"><i class="fa-solid fa-bullseye"></i></div>
                    <div class="wc-rich-body"><div class="wc-rich-title"><h4><a href="{{ route('webcatalogue.recognition.leads.show', $lead) }}">{{ $lead->brand ?: 'Unknown brand' }}</a></h4><span class="wc-badge">{{ $lead->status }}</span></div><div class="wc-rich-description">{{ $lead->model ?: $lead->reference ?: 'No model/reference provided' }}</div></div>
                </div>
            @empty
                <div class="wc-list-empty"><i class="fa-solid fa-lightbulb"></i><div><strong>No unmatched leads.</strong></div></div>
            @endforelse
        </div>
    </aside>
</div>
</div>
@endsection
