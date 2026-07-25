@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-masonry-visual">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
            @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product))
            @php($priceValue = $price && ($price->regular_price || $price->sale_price) ? ($price->sale_price ?: $price->regular_price) : ($product->price ?? null))
            @php($priceCurrency = $price->currency ?? 'EUR')
            @php($productUrl = \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null))
            @php($isTcgStore = isset($store) && ($store->slug ?? null) === 'tcg-collectors')
            @php($cardMeta = is_array($product->metadata ?? null) ? $product->metadata : [])
            <article class="wc-masonry-card">
                <a class="wc-card-product-link" href="{{ $productUrl }}">
                    @if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<div class="wc-thumb-icon"><i class="fa-solid fa-image"></i></div>@endif
                    <div>
                        <h3>{!! $product->name !!}</h3>
                        @if($isTcgStore)
                            <p>{{ $product->category ?? 'Card' }}</p>
                            <span class="wc-card-tags">
                                @if(!empty($cardMeta['set_code']))<em>{{ strtoupper($cardMeta['set_code']) }}</em>@endif
                                @if(!empty($cardMeta['collector_number']))<em>#{{ $cardMeta['collector_number'] }}</em>@endif
                                @if(!empty($cardMeta['rarity']))<em>{{ ucfirst($cardMeta['rarity']) }}</em>@endif
                                @if(!empty($cardMeta['mana_cost']))<em>{{ $cardMeta['mana_cost'] }}</em>@endif
                            </span>
                        @else
                            <p>{{ $product->brand ?? $product->category ?? $product->reference }}</p>
                            <span>@if($flags['model'])3D @endif @if($flags['ar'])AR @endif @if($flags['vr'])VR @endif</span>
                        @endif
                    </div>
                </a>
                @if($isTcgStore)
                    <div class="wc-card-commerce">
                        @if($priceValue)
                            <strong>{{ number_format((float) $priceValue, 2, ',', ' ') }} {{ $priceCurrency }}</strong>
                        @else
                            <strong class="is-muted">Consultar</strong>
                        @endif
                        <button class="wc-demo-buy-button" type="button"><i class="fa-solid fa-cart-shopping"></i> Comprar</button>
                    </div>
                @endif
            </article>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif

@once
    @push('styles')
        <style>
            .wc-masonry-card .wc-card-product-link{display:block;color:inherit}
            .wc-masonry-card .wc-card-product-link>img{width:100%;height:auto;display:block;background:var(--wc-image-bg)}
            .wc-masonry-card .wc-card-product-link>div{padding:14px}
            .wc-masonry-card>.wc-card-commerce{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 14px 14px;padding-top:10px;border-top:1px solid var(--wc-border)}
            .wc-masonry-card .wc-card-commerce strong{min-width:0;color:var(--wc-text);font-size:14px;font-weight:950}
            .wc-masonry-card .wc-card-commerce strong.is-muted{color:var(--wc-muted)}
            .wc-demo-buy-button{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:0;border-radius:var(--wc-radius-sm);background:var(--wc-dark);color:#fff;padding:7px 9px;font:inherit;font-size:11px;font-weight:950;white-space:nowrap;cursor:default}
        </style>
    @endpush
@endonce
