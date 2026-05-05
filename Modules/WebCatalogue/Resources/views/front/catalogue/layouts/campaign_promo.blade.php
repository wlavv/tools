@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-campaign-hero-strip"><strong>Campaign selection</strong><span>Interactive products, pricing and immersive resources.</span></div>
    <div class="wc-campaign-grid">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product))
            <a class="wc-campaign-card" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <div class="wc-campaign-badge"><i class="fa-solid fa-bullhorn"></i> Featured</div>
                <div class="wc-thumb">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<div class="wc-thumb-icon"><i class="fa-solid fa-tag"></i></div>@endif</div>
                <div><h3>{!! $product->name !!}</h3><p>{{ $product->short_description ? strip_tags($product->short_description) : ($product->brand ?? $product->category) }}</p>@if($price && $price->regular_price)<div class="wc-price">{{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}</div>@endif</div>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
