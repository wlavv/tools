@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    @php($featured = $products->first())
    @if($featured)
        @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($featured))
        @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($featured))
        @php($price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($featured))
        <a class="wc-premium-feature wc-card" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($featured, $store, $catalogue ?? null) }}">
            <div class="wc-premium-copy">
                <div class="wc-eyebrow">Featured product</div>
                <h2>{!! $featured->name !!}</h2>
                <p>{{ $featured->brand ?? $featured->category ?? $featured->reference }}</p>
                <div class="wc-resource-status">@foreach(['model'=>'3D','ar'=>'AR','vr'=>'VR','video'=>'Video','audio'=>'Audio'] as $k=>$label)<span class="@if($flags[$k]) is-on @endif">{{ $label }}</span>@endforeach</div>
                @if($price && $price->regular_price)<div class="wc-price">{{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}</div>@endif
            </div>
            <div class="wc-premium-image wc-thumb">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($featured->name) }}">@else<div class="wc-thumb-icon"><i class="fa-solid fa-box-open"></i></div>@endif</div>
        </a>
    @endif
    <div class="wc-premium-row">
        @foreach($products->getCollection()->skip(1) as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
            <a class="wc-card wc-premium-mini" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <div class="wc-thumb">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<div class="wc-thumb-icon"><i class="fa-solid fa-box-open"></i></div>@endif</div>
                <div><h3>{!! $product->name !!}</h3><p>{{ $product->brand ?? $product->reference }}</p><div class="wc-capability-row"><span class="@if($flags['model']) is-on @endif"><i class="fa-solid fa-cube"></i></span><span class="@if($flags['ar']) is-on @endif"><i class="fa-solid fa-vr-cardboard"></i></span><span class="@if($flags['vr']) is-on @endif"><i class="fa-solid fa-headset"></i></span></div></div>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
