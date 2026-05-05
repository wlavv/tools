@extends('webcatalogue::front.layouts.app')

@section('title', ($catalogue->name ?? 'Catalogue') . ' · ' . ($store->name ?? 'WebCatalogue'))

@section('content')
@php
    $frontMeta = is_array($store->metadata ?? null) ? (($store->metadata['front'] ?? []) ?: []) : [];
    $layoutKey = preg_replace('/[^a-z0-9_\-]/i', '', (string)($frontMeta['layout'] ?? 'classic_catalogue')) ?: 'classic_catalogue';
    $layoutView = 'webcatalogue::front.catalogue.layouts.' . $layoutKey;
    if (!view()->exists($layoutView)) {
        $layoutView = 'webcatalogue::front.catalogue.layouts.classic_catalogue';
    }
@endphp
<div class="wc-front-container">
    <section class="wc-hero wc-layout-hero wc-layout-hero-{{ $layoutKey }}">
        <div class="wc-hero-card">
            <div class="wc-eyebrow">Catalogue · {{ config('webcatalogue_front_layouts.' . $layoutKey . '.label', 'Classic Catalogue') }}</div>
            <h1 class="wc-title">{!! $catalogue->name !!}</h1>
            @if(!empty($catalogue->description))
                <div class="wc-lead">{!! $catalogue->description !!}</div>
            @else
                <p class="wc-lead">Interactive product catalogue with rich media, commercial details and immersive viewers.</p>
            @endif
            <div class="wc-metrics">
                <div class="wc-metric"><strong>{{ $products->total() }}</strong><span>Products</span></div>
                <div class="wc-metric"><strong>{{ $catalogue->catalogue_type ?? 'showcase' }}</strong><span>Type</span></div>
                <div class="wc-metric"><strong>{{ $catalogue->price_mode ?? 'visible' }}</strong><span>Price mode</span></div>
            </div>
        </div>
        <div class="wc-card wc-panel">
            <div class="wc-eyebrow">Store</div>
            <h2 style="margin-top:0">{{ $store->name }}</h2>
            <p class="wc-richtext">{{ $store->domain ?? $store->code ?? $store->slug }}</p>
            <div class="wc-actions"><a class="wc-btn" href="{{ route('webcatalogue.front.store.show', $store->slug) }}">All catalogues</a></div>
        </div>
    </section>

    <h2 class="wc-section-title">Catalogue products</h2>
    @include('webcatalogue::front.catalogue._filters', ['filters' => $filters ?? []])
    @include($layoutView, ['products' => $products, 'store' => $store, 'catalogue' => $catalogue])
</div>
@endsection
