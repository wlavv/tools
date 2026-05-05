@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-editor-layout">
    <div>
        <div class="wc-detail-hero wc-detail-hero-product">
            <div>
                <span class="wc-eyebrow"><i class="fa-solid fa-box-open"></i> Product</span>
                <h2 class="wc-html-title">{!! $item->name ?? 'Product' !!}</h2>
                <div class="wc-html-content wc-html-summary">{!! $item->short_description ?: ($item->reference ?? 'WebCatalogue product record') !!}</div>
                <div class="wc-detail-tags">
                    <span class="wc-badge">{{ $item->status ?? 'draft' }}</span>
                    @if($item->reference)<span class="wc-badge">Ref: {{ $item->reference }}</span>@endif
                    @if($item->brand)<span class="wc-badge">{{ $item->brand }}</span>@endif
                    @if($item->category)<span class="wc-badge">{{ $item->category }}</span>@endif
                </div>
                @if(($item->resources ?? collect())->where('resource_type', 'model_3d')->count())
                    <div class="wc-actions-row" style="margin-top:14px">
                        <a class="wc-primary-btn" href="{{ route('webcatalogue.products.viewer', $item) }}"><i class="fa-solid fa-cube"></i> View 3D</a>
                    </div>
                @endif
            </div>
            <div class="wc-detail-icon"><i class="fa-solid fa-cube"></i></div>
        </div>

        <div class="wc-detail-grid">
            <div class="wc-info-card"><i class="fa-solid fa-store"></i><span>Store</span><strong>{{ $item->store->name ?? $item->id_store ?? '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-barcode"></i><span>SKU / EAN</span><strong>{{ $item->sku ?: '—' }} / {{ $item->ean13 ?: '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-euro-sign"></i><span>Base price</span><strong>{{ $item->price !== null ? number_format((float)$item->price, 2, ',', ' ') . ' ' . ($item->currency ?? 'EUR') : '—' }}</strong></div>
            <div class="wc-info-card"><i class="fa-solid fa-boxes-stacked"></i><span>Stock</span><strong>{{ $item->stock ?? '—' }}</strong></div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-align-left"></i> Description</span><h3>Product content</h3></div></div>
            <div class="wc-html-content">{!! $item->description ?: '<p class="wc-muted">No long description has been added yet.</p>' !!}</div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-tags"></i> Pricing</span><h3>Commercial prices</h3></div><a class="wc-action-link" href="{{ route('webcatalogue.products.edit', $item) }}#commercial-pricing"><i class="fa-solid fa-pencil"></i> Edit on product</a></div>
            <div class="wc-inline-list">
                @forelse($item->prices ?? [] as $price)
                    <div class="wc-inline-row">
                        <div><strong>{{ ucfirst(str_replace('_',' ', $price->price_type ?? 'standard')) }}</strong><span>{{ $price->status ?? 'active' }}</span></div>
                        <div class="wc-price-chip">{{ $price->regular_price !== null ? number_format((float)$price->regular_price, 2, ',', ' ') : '—' }} {{ $price->currency ?? 'EUR' }} @if($price->sale_price)<small>Promo {{ number_format((float)$price->sale_price, 2, ',', ' ') }}</small>@endif</div>
                    </div>
                @empty
                    <div class="wc-empty-state"><i class="fa-solid fa-tag"></i><span>No product price rules yet.</span></div>
                @endforelse
            </div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-bullhorn"></i> Promotions</span><h3>Promotion links</h3></div><a class="wc-action-link" href="{{ route('webcatalogue.products.edit', $item) }}#commercial-promotions"><i class="fa-solid fa-pencil"></i> Edit on product</a></div>
            <div class="wc-inline-list">
                @forelse($item->promotions ?? [] as $promotion)
                    <div class="wc-inline-row">
                        <div><strong>{{ $promotion->name }}</strong><span>{{ $promotion->badge_label ?: ucfirst($promotion->promotion_type ?? 'campaign') }}</span></div>
                        <span class="wc-badge">{{ $promotion->status ?? 'draft' }}</span>
                    </div>
                @empty
                    <div class="wc-empty-state"><i class="fa-solid fa-percent"></i><span>No active promotion attached to this product.</span></div>
                @endforelse
            </div>
        </div>
    </div>

    <aside class="wc-preview-panel">
        @php
            $mediaResources = ($item->resources ?? collect())->filter(function($resource){
                return in_array($resource->resource_type, ['image','gallery_image','thumbnail','cover']) || str_contains((string) $resource->mime_type, 'image');
            })->values();
            $main = $mediaResources->firstWhere('is_main', true) ?: $mediaResources->first();
            $mediaUrl = function($resource){ return $resource ? ($resource->public_url ?: asset('storage/'.$resource->file_path)) : null; };
        @endphp
        <div class="wc-preview-card wc-product-gallery-card" data-wc-gallery>
            <div class="wc-preview-media wc-product-gallery-main">
                @if($main)
                    <img data-wc-gallery-main data-wc-lightbox-open src="{{ $mediaUrl($main) }}" alt="{{ strip_tags($main->title ?? $item->name) }}">
                @else
                    <i class="fa-solid fa-cube wc-preview-icon"></i>
                @endif
            </div>
            <div class="wc-preview-body"><h4>Product gallery</h4><p class="wc-muted">{{ $mediaResources->count() }} image(s) associated with this product.</p></div>
            @if($mediaResources->count() > 1)
                <div class="wc-gallery-controls"><button type="button" data-wc-gallery-prev><i class="fa-solid fa-angle-left"></i></button><button type="button" data-wc-gallery-next><i class="fa-solid fa-angle-right"></i></button></div>
                <div class="wc-gallery-thumbs">
                    @foreach($mediaResources as $idx => $resource)
                        <button type="button" class="wc-gallery-thumb @if($idx===0) wc-is-active @endif" data-wc-gallery-src="{{ $mediaUrl($resource) }}" data-wc-lightbox-thumb><img src="{{ $mediaUrl($resource) }}" alt="{{ strip_tags($resource->title ?? 'Image') }}"></button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="wc-preview-card"><div class="wc-preview-body"><h4>Resources</h4><div class="wc-preview-list">
            @forelse(($item->resources ?? collect())->take(12) as $resource)
                <div class="wc-preview-item"><i class="fa-solid fa-{{ in_array($resource->resource_type, ['image','gallery_image','cover','thumbnail']) ? 'image' : (str_contains($resource->resource_type, 'audio') ? 'volume-high' : (str_contains($resource->resource_type, 'video') ? 'video' : ($resource->resource_type === 'model_3d' ? 'cube' : ($resource->resource_type === 'ar_file' ? 'mobile-screen' : ($resource->resource_type === 'vr_file' ? 'vr-cardboard' : 'file'))))) }}"></i><span>{{ $resource->title ?: $resource->resource_type }}</span></div>
            @empty
                <div class="wc-preview-item"><i class="fa-solid fa-circle-info"></i><span>No resources yet.</span></div>
            @endforelse
        </div></div></div>
    </aside>
</div>
<div class="wc-lightbox" data-wc-lightbox hidden aria-hidden="true">
    <div class="wc-lightbox-backdrop" data-wc-lightbox-close></div>
    <div class="wc-lightbox-dialog" role="dialog" aria-modal="true" aria-label="Product image gallery">
        <button type="button" class="wc-lightbox-close" data-wc-lightbox-close aria-label="Close image preview"><i class="fa-solid fa-xmark"></i></button>
        <button type="button" class="wc-lightbox-nav wc-lightbox-prev" data-wc-lightbox-prev aria-label="Previous image"><i class="fa-solid fa-angle-left"></i></button>
        <img data-wc-lightbox-image src="" alt="Product image preview">
        <button type="button" class="wc-lightbox-nav wc-lightbox-next" data-wc-lightbox-next aria-label="Next image"><i class="fa-solid fa-angle-right"></i></button>
        <div class="wc-lightbox-footer"><span data-wc-lightbox-counter>1 / 1</span><span class="wc-muted">Click outside or press ESC to close</span></div>
    </div>
</div>

</div>
@endsection
