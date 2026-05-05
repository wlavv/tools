@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    <div class="wc-dark-stage">
        @foreach($products as $product)
            @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
            @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
            <a class="wc-dark-tile" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}">
                <div class="wc-thumb">@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<div class="wc-thumb-icon"><i class="fa-solid fa-cube"></i></div>@endif</div>
                <div class="wc-dark-overlay"><h3>{!! $product->name !!}</h3><p>{{ $product->reference }}</p><div class="wc-resource-status"><span class="@if($flags['model']) is-on @endif">3D</span><span class="@if($flags['ar']) is-on @endif">AR</span><span class="@if($flags['vr']) is-on @endif">VR</span></div></div>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
