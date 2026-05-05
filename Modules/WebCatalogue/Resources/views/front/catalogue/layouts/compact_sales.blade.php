@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-compact-sales">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product))
            @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
            <a class="wc-compact-item" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <span class="wc-compact-thumb">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<i class="fa-solid fa-box"></i>@endif</span>
                <span><strong>{!! $product->name !!}</strong><small>{{ $product->reference }} · {{ $product->brand }}</small></span>
                <span class="wc-compact-flags">@if($flags['model'])<i class="fa-solid fa-cube"></i>@endif @if($flags['ar'])<i class="fa-solid fa-vr-cardboard"></i>@endif @if($flags['vr'])<i class="fa-solid fa-headset"></i>@endif</span>
                <strong>@if($price && $price->regular_price){{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}@endif</strong>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
