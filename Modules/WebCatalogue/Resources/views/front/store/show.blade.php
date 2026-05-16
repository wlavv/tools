@extends('webcatalogue::front.layouts.app')

@section('title', ($store->name ?? 'Store') . ' · WebCatalogue')

@section('content')
@php
    $front = is_array($store->metadata ?? null) ? (($store->metadata['front'] ?? []) ?: []) : [];
    $layoutKey = preg_replace('/[^a-z0-9_\-]/i', '', (string)($front['layout'] ?? 'classic_catalogue')) ?: 'classic_catalogue';
    $layoutView = 'webcatalogue::front.catalogue.layouts.' . $layoutKey;
    if (!view()->exists($layoutView)) {
        $layoutView = 'webcatalogue::front.catalogue.layouts.classic_catalogue';
    }
@endphp
<div class="wc-front-container">
    @if(!empty($isPreview))
        <div class="wc-front-preview-banner">
            <strong>Preview</strong>
            <span>This catalogue is protected by a preview token and is not the public published link.</span>
        </div>
    @endif
    <section class="wc-hero wc-layout-hero wc-layout-hero-{{ $layoutKey }}">
        <div class="wc-hero-card">
            <div class="wc-eyebrow">Visual catalogue · {{ config('webcatalogue_front_layouts.' . $layoutKey . '.label', 'Classic Catalogue') }}</div>
            <h1 class="wc-title">{!! $store->name !!}</h1>
            <p class="wc-lead">{{ $front['intro_text'] ?? 'Explore interactive catalogues, product details, 3D models, AR experiences and immersive resources.' }}</p>
            <div class="wc-metrics">
                <div class="wc-metric"><strong>{{ $catalogues->count() }}</strong><span>Catalogues</span></div>
                <div class="wc-metric"><strong>{{ $products->total() }}</strong><span>Products</span></div>
                <div class="wc-metric"><strong>3D</strong><span>Ready front</span></div>
            </div>
        </div>
        <div class="wc-card wc-panel">
            <div class="wc-eyebrow">Catalogues</div>
            <div class="wc-resource-list">
                @forelse($catalogues as $catalogue)
                    <a class="wc-resource-item" href="{{ route('webcatalogue.front.catalogue.show', [$store->slug, $catalogue->slug]) }}">
                        <span><strong>{{ $catalogue->name }}</strong><br><small>{{ $catalogue->status ?? 'draft' }}</small></span>
                        <span>→</span>
                    </a>
                @empty
                    <div class="wc-empty">No catalogues available yet.</div>
                @endforelse
            </div>
        </div>
    </section>

    <h2 class="wc-section-title">Products</h2>
    @include('webcatalogue::front.catalogue._filters', ['filters' => $filters ?? []])
    @include($layoutView, ['products' => $products, 'store' => $store, 'catalogue' => null])
</div>
@endsection
