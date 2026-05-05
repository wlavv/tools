@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-editor-layout">
    <div>
        <div class="wc-detail-hero wc-detail-hero-resource">
            <div>
                <span class="wc-eyebrow"><i class="fa-solid fa-photo-film"></i> Resource</span>
                <h2>{{ $item->name ?? $item->title ?? $item->reference ?? 'Resource' }}</h2>
                <p>{{ $item->description ?? $item->short_description ?? 'Structured WebCatalogue record.' }}</p>
                <div class="wc-detail-tags"><span class="wc-badge">{{ $item->status ?? '—' }}</span>@if(!empty($item->id_store))<span class="wc-badge">Store #{{ $item->id_store }}</span>@endif</div>
            </div>
            <div class="wc-detail-icon"><i class="fa-solid fa-photo-film"></i></div>
        </div>

        <div class="wc-detail-grid">
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Resource Type</span><strong>{{ $item->resource_type ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Source Type</span><strong>{{ $item->source_type ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Filename</span><strong>{{ $item->filename ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>Mime Type</span><strong>{{ $item->mime_type ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-circle-info"></i><span>File Size</span><strong>{{ $item->file_size ?? '—' }}</strong></div>
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
            <div class="wc-preview-media">
                @if(str_contains((string)$item->mime_type, 'image') || in_array($item->resource_type, ['image','gallery_image','thumbnail','cover']))
                    <img src="{{ $item->public_url ?: asset('storage/'.$item->file_path) }}" alt="{{ $item->title }}">
                @elseif(str_contains((string)$item->mime_type, 'audio') || in_array($item->resource_type, ['audio','ambient_audio','voiceover','music_track','sound_effect']))
                    <audio controls src="{{ $item->public_url ?: asset('storage/'.$item->file_path) }}"></audio>
                @elseif(str_contains((string)$item->mime_type, 'video') || $item->resource_type === 'video')
                    <video controls src="{{ $item->public_url ?: asset('storage/'.$item->file_path) }}"></video>
                @elseif(in_array($item->resource_type, ['model_3d','ar_file','vr_file','skybox']))
                    <i class="fa-solid fa-cube wc-preview-icon"></i>
                @else
                    <i class="fa-solid fa-file wc-preview-icon"></i>
                @endif
            </div>
            <div class="wc-preview-body"><h4>Resource preview</h4><p class="wc-muted">{{ $item->filename ?: $item->source_url ?: 'No file attached.' }}</p></div>
        </div>
        <div class="wc-preview-card"><div class="wc-preview-body"><h4>Actions</h4><div class="wc-actions-row"><a class="wc-action-link" href="{{ route('webcatalogue.resources.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a><a class="wc-action-link" href="{{ route('webcatalogue.resources.index') }}"><i class="fa-solid fa-angle-left"></i> Back</a></div></div></div>
    </aside>
</div>
</div>
@endsection
