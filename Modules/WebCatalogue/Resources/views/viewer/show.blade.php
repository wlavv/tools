@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')

@php
    $modelUrl = $model?->resolved_url;
    $arUrl = $ar?->resolved_url;
    $posterUrl = $thumbnail?->resolved_url;
    $productTitle = strip_tags($product->name ?? 'Product viewer');
@endphp

<div class="webcatalogue-shell wc-immersive-viewer-shell">
    <div class="wc-viewer-hero">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-cube"></i> WebCatalogue Viewer</span>
            <h2>{!! $product->name ?? '3D Product Viewer' !!}</h2>
            <p>{{ $product->reference ? 'Reference: '.$product->reference : 'Interactive 3D, AR and VR product experience.' }}</p>
            <div class="wc-viewer-meta">
                <span><i class="fa-solid fa-store"></i> {{ $product->store->name ?? 'Store not defined' }}</span>
                <span><i class="fa-solid fa-layer-group"></i> {{ $product->resources->count() }} resources</span>
                @if($model)<span><i class="fa-solid fa-check"></i> GLB available</span>@endif
                @if($ar)<span><i class="fa-solid fa-mobile-screen"></i> AR available</span>@endif
                @if($vr)<span><i class="fa-solid fa-headset"></i> VR resource available</span>@endif
            </div>
        </div>
        <div class="wc-viewer-hero-icon"><i class="fa-solid fa-vr-cardboard"></i></div>
    </div>

    @if(!$modelUrl)
        <div class="wc-card wc-error-card">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div>
                <h3>No 3D model available</h3>
                <p>This product does not have a <strong>model_3d</strong> resource yet. Generate one in 3D Studio or upload a GLB/GLTF resource.</p>
                <a class="wc-primary-btn" href="{{ route('webcatalogue.studio.3d_jobs.create') }}"><i class="fa-solid fa-wand-magic-sparkles"></i> Create 3D job</a>
            </div>
        </div>
    @else
        <div class="wc-viewer-layout">
            <section class="wc-viewer-stage-card">
                <div class="wc-viewer-toolbar">
                    <div>
                        <strong>{{ $productTitle }}</strong>
                        <span>{{ $model->filename ?? basename(parse_url($modelUrl, PHP_URL_PATH) ?: 'model.glb') }}</span>
                    </div>
                    <div class="wc-viewer-actions">
                        <button type="button" class="wc-action-link btn-outline-primary" data-wc-model-reset><i class="fa-solid fa-rotate-left"></i> Reset</button>
                        <button type="button" class="wc-action-link btn-outline-primary" data-wc-model-fullscreen><i class="fa-solid fa-expand"></i> Fullscreen</button>
                        @if($modelUrl)<a class="wc-action-link btn-outline-primary" href="{{ $modelUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-download"></i> Open GLB</a>@endif
                    </div>
                </div>

                <div class="wc-model-viewer-wrap" data-wc-viewer-wrap>
                    <model-viewer
                        id="wcModelViewer"
                        src="{{ $modelUrl }}"
                        @if($posterUrl) poster="{{ $posterUrl }}" @endif
                        @if($arUrl) ios-src="{{ $arUrl }}" @endif
                        alt="{{ $productTitle }}"
                        camera-controls
                        touch-action="pan-y"
                        auto-rotate
                        ar
                        ar-modes="webxr scene-viewer quick-look"
                        shadow-intensity="1"
                        exposure="1"
                        environment-image="neutral"
                        loading="eager"
                        reveal="auto"
                    >
                        <button class="wc-ar-button" slot="ar-button"><i class="fa-solid fa-mobile-screen"></i> View in AR</button>
                        <div class="wc-model-progress" slot="progress-bar"><div class="wc-model-progress-bar"></div></div>
                    </model-viewer>
                </div>
            </section>

            <aside class="wc-viewer-side">
                <div class="wc-card">
                    <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-circle-info"></i> Details</span><h3>Viewer resources</h3></div></div>
                    <div class="wc-preview-list">
                        <div class="wc-preview-item"><i class="fa-solid fa-cube"></i><span>3D model: {{ $model->filename ?? 'GLB resource' }}</span></div>
                        @if($ar)<div class="wc-preview-item"><i class="fa-solid fa-mobile-screen"></i><span>AR file: {{ $ar->filename ?? 'USDZ / AR resource' }}</span></div>@endif
                        @if($vr)<div class="wc-preview-item"><i class="fa-solid fa-headset"></i><span>VR file: {{ $vr->filename ?? 'VR resource' }}</span></div>@endif
                    </div>
                </div>

                <div class="wc-card wc-spaced-card">
                    <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-images"></i> Product media</span><h3>Gallery</h3></div></div>
                    <div class="wc-viewer-gallery">
                        @forelse($gallery->take(8) as $image)
                            <a href="{{ $image->resolved_url }}" target="_blank" rel="noopener"><img src="{{ $image->resolved_url }}" alt="{{ strip_tags($image->title ?? $productTitle) }}"></a>
                        @empty
                            <div class="wc-empty-state"><i class="fa-solid fa-image"></i><span>No images available.</span></div>
                        @endforelse
                    </div>
                </div>

                <div class="wc-card wc-spaced-card">
                    <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-align-left"></i> Description</span><h3>Product content</h3></div></div>
                    <div class="wc-html-content">{!! $product->short_description ?: ($product->description ?: '<p class="wc-muted">No product description available.</p>') !!}</div>
                </div>
            </aside>
        </div>
    @endif
</div>

<script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewer = document.getElementById('wcModelViewer');
    const wrap = document.querySelector('[data-wc-viewer-wrap]');
    const reset = document.querySelector('[data-wc-model-reset]');
    const fullscreen = document.querySelector('[data-wc-model-fullscreen]');

    if (viewer && reset) {
        reset.addEventListener('click', function () {
            viewer.cameraOrbit = '0deg 75deg 105%';
            viewer.cameraTarget = 'auto auto auto';
            viewer.fieldOfView = 'auto';
        });
    }

    if (wrap && fullscreen) {
        fullscreen.addEventListener('click', function () {
            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }
            wrap.requestFullscreen && wrap.requestFullscreen();
        });
    }
});
</script>
@endsection
