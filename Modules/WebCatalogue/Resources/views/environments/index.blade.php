@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
<div class="wc-card">
    <div class="wc-list-toolbar"><div><span class="wc-eyebrow"><i class="fa-solid fa-vr-cardboard"></i> Immersive layer</span><h3>Environments</h3><p class="wc-muted">3D backgrounds, VR ambiences, lighting presets and AR/VR scene configuration.</p></div></div>
    <div class="wc-rich-list">
    @forelse($items as $item)
        <div class="wc-rich-card">
            <div class="wc-rich-media" style="background:linear-gradient(135deg, {{ $item->background_color ?: '#4338ca' }}, #111827);"><i class="fa-solid fa-cubes-stacked" style="color:rgba(255,255,255,.72)"></i></div>
            <div class="wc-rich-body">
                <div class="wc-rich-title"><h4><a href="{{ route('webcatalogue.environments.show', $item) }}">{{ $item->name ?? 'Environment #'.$item->id }}</a></h4><span class="wc-badge wc-status-{{ $item->status ?? 'active' }}">{{ $item->status ?? 'active' }}</span>@if($item->is_default)<span class="wc-badge wc-status-active">Default</span>@endif</div>
                <div class="wc-rich-description">{{ $item->environment_type ?? '3D scene' }} · {{ $item->background_type ?? 'background' }} · {{ $item->lighting_preset ?? 'standard light' }}</div>
                <div class="wc-rich-meta"><span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $item->store->name ?? 'No store' }}</span><span class="wc-rich-metric"><i class="fa-solid fa-camera"></i>{{ $item->camera_preset ?: 'No camera preset' }}</span><span class="wc-rich-metric"><i class="fa-solid fa-lightbulb"></i>{{ $item->lighting_preset ?: 'No lighting preset' }}</span></div>
            </div>
            <div class="wc-rich-actions"><a class="wc-action-link" href="{{ route('webcatalogue.environments.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a><a class="wc-action-link" href="{{ route('webcatalogue.environments.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a></div>
        </div>
    @empty
        <div class="wc-list-empty"><i class="fa-solid fa-vr-cardboard"></i><div><strong>No environments yet.</strong><br><span>Create a 3D/VR environment for product presentation.</span></div></div>
    @endforelse
    </div>
    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)<div class="wc-pagination">{{ $items->links() }}</div>@endif
</div>
</div>
@endsection
