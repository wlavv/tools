@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-luxury-layout">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product))
            <a class="wc-luxury-card" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <div class="wc-luxury-image">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<i class="fa-solid fa-gem"></i>@endif</div>
                <div class="wc-luxury-copy"><small>{{ $product->brand ?? 'Collection' }}</small><h3>{!! $product->name !!}</h3><p>{{ $product->category ?? $product->reference }}</p>@if($price && $price->regular_price)<strong>{{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}</strong>@endif</div>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
