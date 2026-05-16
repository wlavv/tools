@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-editor-layout">
    <div>
        <div class="wc-detail-hero wc-detail-hero-environment">
            <div>
                <span class="wc-eyebrow"><i class="fa-solid fa-vr-cardboard"></i> Environment</span>
                <h2>{{ $item->name ?? $item->title ?? $item->reference ?? 'Environment' }}</h2>
                <p>{{ $item->description ?? $item->short_description ?? 'Structured WebCatalogue record.' }}</p>
                <div class="wc-detail-tags"><span class="wc-badge">{{ $item->status ?? '—' }}</span>@if(!empty($item->id_store))<span class="wc-badge">Store #{{ $item->id_store }}</span>@endif</div>
            </div>
            <div class="wc-detail-icon"><i class="fa-solid fa-vr-cardboard"></i></div>
        </div>

        <div class="wc-detail-grid">
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Slug</span><strong>{{ $item->slug ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Environment Type</span><strong>{{ $item->environment_type ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Background Type</span><strong>{{ $item->background_type ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Lighting Preset</span><strong>{{ $item->lighting_preset ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Status</span><strong>{{ $item->status ?? '—' }}</strong></div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-list-check"></i> Details</span><h3>Record information</h3></div></div>
            <div class="wc-keyval-grid">
                @foreach($item->getAttributes() as $key => $value)
                    @continue(in_array($key, ['metadata','description','short_description','created_at','updated_at']))
                    <div class="wc-keyval"><span>{{ str_replace('_', ' ', $key) }}</span><strong>{{ is_scalar($value) || is_null($value) ? ($value ?? '—') : json_encode($value) }}</strong></div>
                @endforeach
            </div>
        </div>

        @if(!empty($item->metadata))
        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-code"></i> Metadata</span><h3>Advanced metadata</h3></div></div>
            <pre class="wc-json-preview">{{ json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif
    </div>

    <aside class="wc-preview-panel">
        
        <div class="wc-preview-card">
            <div class="wc-preview-media"><i class="fa-solid fa-vr-cardboard wc-preview-icon"></i></div>
            <div class="wc-preview-body"><h4>Environment preview</h4><p class="wc-muted">Visual summary of this record.</p></div>
        </div>
    </aside>
</div>
</div>
@endsection
