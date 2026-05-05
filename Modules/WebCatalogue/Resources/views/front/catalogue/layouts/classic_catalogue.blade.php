@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-layout-grid wc-grid wc-real-layout wc-classic-grid">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
            @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product))
            <a class="wc-card wc-product-card" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <div class="wc-thumb">
                    @if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}" loading="lazy" draggable="false">@else<div class="wc-thumb-icon"><i class="fa-solid fa-box-open"></i></div>@endif
                    <div class="wc-resource-ribbon">@if($flags['model'])<span><i class="fa-solid fa-cube"></i> 3D</span>@endif @if($flags['ar'])<span><i class="fa-solid fa-vr-cardboard"></i> AR</span>@endif @if($flags['vr'])<span><i class="fa-solid fa-headset"></i> VR</span>@endif</div>
                </div>
                <div class="wc-card-body">
                    <h3 class="wc-card-title">{!! $product->name !!}</h3>
                    <div class="wc-card-meta">@if($product->reference)<span class="wc-badge">{{ $product->reference }}</span>@endif @if($product->brand)<span class="wc-badge">{{ $product->brand }}</span>@endif @if($product->category)<span class="wc-badge">{{ $product->category }}</span>@endif</div>
                    <div class="wc-capability-row"><span class="@if($flags['image']) is-on @endif"><i class="fa-solid fa-image"></i></span><span class="@if($flags['model']) is-on @endif"><i class="fa-solid fa-cube"></i></span><span class="@if($flags['ar']) is-on @endif"><i class="fa-solid fa-vr-cardboard"></i></span><span class="@if($flags['vr']) is-on @endif"><i class="fa-solid fa-headset"></i></span><span class="@if($flags['video']) is-on @endif"><i class="fa-solid fa-video"></i></span><span class="@if($flags['audio']) is-on @endif"><i class="fa-solid fa-volume-high"></i></span></div>
                    @if($price && $price->regular_price)<div class="wc-price">{{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}</div>@endif
                </div>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
