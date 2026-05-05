@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-masonry-visual">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
            <a class="wc-masonry-card" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                @if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<div class="wc-thumb-icon"><i class="fa-solid fa-image"></i></div>@endif
                <div><h3>{!! $product->name !!}</h3><p>{{ $product->brand ?? $product->category ?? $product->reference }}</p><span>@if($flags['model'])3D @endif @if($flags['ar'])AR @endif @if($flags['vr'])VR @endif</span></div>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
