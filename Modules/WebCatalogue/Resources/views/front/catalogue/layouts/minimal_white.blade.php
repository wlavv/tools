@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-minimal-list">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product))
            <a class="wc-minimal-item" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <div class="wc-minimal-thumb">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<span><i class="fa-solid fa-box-open"></i></span>@endif</div>
                <div class="wc-minimal-info"><h3>{!! $product->name !!}</h3><p>{{ $product->reference }} @if($product->brand) · {{ $product->brand }} @endif @if($product->category) · {{ $product->category }} @endif</p></div>
                @if($price && $price->regular_price)<strong>{{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}</strong>@endif
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
