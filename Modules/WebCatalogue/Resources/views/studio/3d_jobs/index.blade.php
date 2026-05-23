@extends('layouts.app')
@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell wc-studio-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-studio-hero">
    <div>
        <span class="wc-eyebrow"><i class="fa-solid fa-wand-magic-sparkles"></i> WebCatalogue Studio</span>
        <h2>3D Studio</h2>
        <p>Prepare image-to-3D jobs, manage manual GLB/USDZ uploads and keep AR/VR outputs attached to the right product.</p>
        <div class="wc-studio-steps">
            <span><i class="fa-solid fa-images"></i> Source images</span>
            <span><i class="fa-solid fa-cube"></i> 3D model</span>
            <span><i class="fa-solid fa-mobile-screen"></i> AR export</span>
            <span><i class="fa-solid fa-vr-cardboard"></i> VR scene</span>
        </div>
    </div>
    <a class="wc-studio-primary" href="{{ route('webcatalogue.studio.3d_jobs.create') }}"><i class="fa-solid fa-plus"></i><span>New 3D job</span></a>
</div>

<div class="wc-studio-kpis">
    <div class="wc-studio-kpi wc-kpi-blue"><i class="fa-solid fa-list-check"></i><span>Total jobs</span><strong>{{ $items->total() }}</strong></div>
    <div class="wc-studio-kpi wc-kpi-gold"><i class="fa-solid fa-hourglass-half"></i><span>In workflow</span><strong>{{ $items->getCollection()->whereIn('status', ['draft','queued','processing'])->count() }}</strong></div>
    <div class="wc-studio-kpi wc-kpi-green"><i class="fa-solid fa-circle-check"></i><span>Completed on page</span><strong>{{ $items->getCollection()->where('status', 'completed')->count() }}</strong></div>
</div>

<div class="wc-studio-grid">
@forelse($items as $item)
    @php
        $statusIcon = match($item->status) {
            'completed' => 'circle-check',
            'processing' => 'spinner',
            'queued' => 'clock',
            'failed' => 'triangle-exclamation',
            default => 'pen-ruler',
        };
    @endphp
    <article class="wc-studio-job-card wc-status-border-{{ $item->status }}">
        <div class="wc-studio-job-top">
            <div class="wc-studio-job-icon"><i class="fa-solid fa-cube"></i></div>
            <span class="wc-badge wc-status-{{ $item->status }}"><i class="fa-solid fa-{{ $statusIcon }}"></i> {{ $item->status }}</span>
        </div>
        <h4><a href="{{ route('webcatalogue.studio.3d_jobs.show', $item) }}">{{ $item->product?->name ? strip_tags($item->product?->name) : '3D Job #' . $item->id }}</a></h4>
        <p>{{ $item->prompt ?: 'Manual/API generation job prepared for 3D, AR and VR outputs.' }}</p>
        <div class="wc-studio-job-meta">
            <span><i class="fa-solid fa-store"></i>{{ $item->store?->name ?? 'â€”' }}</span>
            <span><i class="fa-solid fa-box-open"></i>{{ $item->product?->reference ?? 'â€”' }}</span>
            <span><i class="fa-solid fa-layer-group"></i>{{ $item->input_mode }}</span>
            <span><i class="fa-solid fa-plug"></i>{{ $item->provider }}</span>
            <span><i class="fa-solid fa-signal"></i>{{ $item->provider_status ?: 'â€”' }} Â· {{ (int)($item->progress ?? 0) }}%</span>
        </div>
        <div class="wc-studio-progress">
            <span class="{{ in_array($item->status, ['draft','queued','processing','completed']) ? 'is-done' : '' }}"><i class="fa-solid fa-images"></i></span>
            <span class="{{ in_array($item->status, ['processing','completed']) ? 'is-done' : '' }}"><i class="fa-solid fa-gears"></i></span>
            <span class="{{ $item->resultResource ? 'is-done' : '' }}"><i class="fa-solid fa-cube"></i></span>
            <span class="{{ ($item->arResource || $item->vrResource) ? 'is-done' : '' }}"><i class="fa-solid fa-vr-cardboard"></i></span>
        </div>
        <div class="wc-studio-card-actions">
            <a class="wc-action-link btn-outline-primary" href="{{ route('webcatalogue.studio.3d_jobs.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a>
            <a class="wc-action-link btn-outline-primary" href="{{ route('webcatalogue.studio.3d_jobs.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a>
        </div>
    </article>
@empty
    <div class="wc-list-empty"><i class="fa-solid fa-cube"></i><div><strong>No 3D jobs yet.</strong><br><span>Create a job from product images or upload a finished GLB/USDZ/VR asset.</span></div></div>
@endforelse
</div>
<div class="wc-pagination">{{ $items->links() }}</div>
</div>
@endsection


