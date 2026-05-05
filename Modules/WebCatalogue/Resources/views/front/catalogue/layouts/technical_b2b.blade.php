@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-tech-table">
        <div class="wc-tech-head"><span>Product</span><span>Reference</span><span>Brand</span><span>Resources</span><span>Price</span></div>
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
            @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product))
            <a class="wc-tech-row" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <span class="wc-tech-product"><span class="wc-tech-img">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<i class="fa-solid fa-box"></i>@endif</span><strong>{!! $product->name !!}</strong></span>
                <span>{{ $product->reference ?? '—' }}</span><span>{{ $product->brand ?? '—' }}</span>
                <span class="wc-tech-icons">@if($flags['model'])<i class="fa-solid fa-cube"></i>@endif @if($flags['ar'])<i class="fa-solid fa-vr-cardboard"></i>@endif @if($flags['vr'])<i class="fa-solid fa-headset"></i>@endif @if($flags['docs'])<i class="fa-solid fa-file-lines"></i>@endif</span>
                <span>@if($price && $price->regular_price){{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}@else — @endif</span>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
