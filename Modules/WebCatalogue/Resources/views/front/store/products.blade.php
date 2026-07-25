@extends('webcatalogue::front.layouts.app')

@section('title', (($store->slug ?? null) === 'tcg-collectors' ? 'Cartas' : 'Products') . ' - ' . ($store->name ?? 'Store'))
@section('body_class', 'wc-store-products-page')

@section('content')
@php
    $front = is_array($store->metadata ?? null) ? (($store->metadata['front'] ?? []) ?: []) : [];
    $layoutKey = preg_replace('/[^a-z0-9_\-]/i', '', (string)($front['layout'] ?? 'classic_catalogue')) ?: 'classic_catalogue';
    $layoutView = 'webcatalogue::front.catalogue.layouts.' . $layoutKey;
    if (!view()->exists($layoutView)) {
        $layoutView = 'webcatalogue::front.catalogue.layouts.classic_catalogue';
    }
    $isTcgStore = ($store->slug ?? null) === 'tcg-collectors';
    $homeUrl = route('webcatalogue.front.store.show', $store->slug);
    $scanUrl = route('webcatalogue.front.scan.index', $store->slug);
@endphp

<div class="wc-front-container wc-store-products-container">
    <section class="wc-products-list-head">
        <div>
            <div class="wc-eyebrow">{{ $isTcgStore ? 'Montra de cartas' : 'Product list' }}</div>
            <h1>{{ $isTcgStore ? 'Cartas disponiveis' : 'Products' }}</h1>
            <p>{{ $isTcgStore ? 'Pesquisa por nome, numero, set, artista ou tipo de carta.' : 'Search and filter the store catalogue.' }}</p>
        </div>
        <div class="wc-products-list-actions">
            <a class="wc-btn" href="{{ $homeUrl }}"><i class="fa-solid fa-house"></i> Home</a>
            <a class="wc-btn wc-btn-gold" href="{{ $scanUrl }}"><i class="fa-solid fa-camera"></i> {{ $isTcgStore ? 'Scan' : 'Open scan' }}</a>
        </div>
    </section>

    @include('webcatalogue::front.catalogue._filters', ['filters' => $filters ?? []])
    @include($layoutView, ['products' => $products, 'store' => $store, 'catalogue' => null])
</div>
@endsection

@push('styles')
<style>
.wc-store-products-page .wc-front-container{width:100%;max-width:none;padding-inline:24px}
.wc-products-list-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:16px;padding:18px;border:1px solid var(--wc-border);border-radius:var(--wc-radius);background:color-mix(in srgb,var(--wc-surface) 92%,transparent);box-shadow:var(--wc-shadow)}
.wc-products-list-head h1{margin:0 0 7px;font-size:clamp(30px,3.4vw,48px);line-height:1.03}
.wc-products-list-head p{margin:0;color:var(--wc-muted);line-height:1.5}
.wc-products-list-actions{display:flex;gap:9px;flex-wrap:wrap;justify-content:flex-end}
.wc-store-products-page .wc-filter-panel{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;align-items:end}
.wc-store-products-page .wc-filter-main{grid-column:1;grid-template-columns:minmax(240px,1fr) minmax(180px,.34fr) minmax(220px,.42fr)}
.wc-store-products-page .wc-filter-actions{grid-column:2;grid-row:1;margin-top:0;align-self:end;flex-wrap:nowrap}
.wc-store-products-page .wc-resource-filters{grid-column:1 / -1;margin-top:0}
.wc-store-products-page .wc-masonry-visual{columns:auto;display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px}
.wc-store-products-page .wc-masonry-card{margin:0}
.wc-store-products-page .wc-card-product-link>img{aspect-ratio:.72;width:100%;height:auto;object-fit:contain;padding:6px}
.wc-store-products-page .wc-card-product-link>div{padding:11px}
.wc-store-products-page .wc-masonry-card h3{font-size:14px;line-height:1.2}
.wc-store-products-page .wc-masonry-card p{font-size:12px;line-height:1.3}
.wc-store-products-page .wc-card-tags em{font-size:10px;padding:4px 7px}
@media(max-width:1400px){.wc-store-products-page .wc-masonry-visual{grid-template-columns:repeat(5,minmax(0,1fr))}}
@media(max-width:1180px){.wc-store-products-page .wc-masonry-visual{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media(max-width:1080px){.wc-store-products-page .wc-filter-panel{grid-template-columns:1fr}.wc-store-products-page .wc-filter-main,.wc-store-products-page .wc-filter-actions,.wc-store-products-page .wc-resource-filters{grid-column:1;grid-row:auto}.wc-store-products-page .wc-filter-actions{justify-content:flex-start}.wc-products-list-head{align-items:flex-start;flex-direction:column}}
@media(max-width:760px){.wc-store-products-page .wc-masonry-visual{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.wc-store-products-page .wc-front-container{padding-inline:12px}.wc-products-list-actions,.wc-products-list-actions .wc-btn{width:100%}.wc-store-products-page .wc-filter-actions{flex-wrap:wrap}.wc-store-products-page .wc-filter-actions .wc-btn{width:100%}}
@media(max-width:430px){.wc-store-products-page .wc-masonry-visual{grid-template-columns:1fr}}
</style>
@endpush
