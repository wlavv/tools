@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-hero-card">
    <div>
        <div class="wc-eyebrow"><i class="fa-solid fa-camera-viewfinder"></i> Visual Recognition</div>
        <h2>Camera-based product discovery</h2>
        <p>Capture product recognition sessions, unmatched product requests and brand prospect demand generated from the public catalogue.</p>
    </div>
    <div class="wc-hero-actions">
        <a class="wc-primary-btn" href="{{ route('webcatalogue.recognition.sessions.index') }}"><i class="fa-solid fa-list"></i> Sessions</a>
        <a class="wc-secondary-btn" href="{{ route('webcatalogue.recognition.leads.index') }}"><i class="fa-solid fa-bullseye"></i> Leads</a>
    </div>
</div>

<div class="wc-grid wc-kpi-grid">
    <div class="wc-kpi-card wc-kpi-card-store"><div class="wc-kpi-content"><h3>Total scans</h3><div class="wc-kpi">{{ $sessionsCount }}</div><div class="wc-muted">All recognition sessions</div></div><i class="fa-solid fa-camera wc-kpi-bg-icon"></i></div>
    <div class="wc-kpi-card wc-kpi-card-catalogue"><div class="wc-kpi-content"><h3>Today</h3><div class="wc-kpi">{{ $todaySessionsCount }}</div><div class="wc-muted">Sessions today</div></div><i class="fa-solid fa-calendar-day wc-kpi-bg-icon"></i></div>
    <div class="wc-kpi-card wc-kpi-card-product"><div class="wc-kpi-content"><h3>New leads</h3><div class="wc-kpi">{{ $newLeadsCount }}</div><div class="wc-muted">Products not found</div></div><i class="fa-solid fa-lightbulb wc-kpi-bg-icon"></i></div>
    <div class="wc-kpi-card wc-kpi-card-resource"><div class="wc-kpi-content"><h3>Brands</h3><div class="wc-kpi">{{ $brandProspectsCount }}</div><div class="wc-muted">Prospect brands</div></div><i class="fa-solid fa-building wc-kpi-bg-icon"></i></div>
</div>

<div class="wc-editor-layout" style="margin-top:16px">
    <div class="wc-card">
        <div class="wc-section-head"><div><h3>Recent sessions</h3><p class="wc-muted">Last camera-based recognition attempts.</p></div></div>
        <div class="wc-rich-list">
            @forelse($recentSessions as $session)
                <div class="wc-rich-card">
                    <div class="wc-rich-media"><i class="fa-solid fa-camera"></i></div>
                    <div class="wc-rich-body"><div class="wc-rich-title"><h4><a href="{{ route('webcatalogue.recognition.sessions.show', $session) }}">Session #{{ $session->id }}</a></h4><span class="wc-badge">{{ $session->status }}</span></div><div class="wc-rich-meta"><span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $session->store->name ?? '—' }}</span><span class="wc-rich-metric"><i class="fa-solid fa-clock"></i>{{ $session->created_at?->format('Y-m-d H:i') }}</span></div></div>
                </div>
            @empty
                <div class="wc-list-empty"><i class="fa-solid fa-camera"></i><div><strong>No sessions yet.</strong><br><span>The public scan page will start populating this list.</span></div></div>
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
