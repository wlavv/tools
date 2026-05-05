@if(!$products->count())
    <div class="wc-empty">No products match the current filters.</div>
@else

    @php($productCollection = $products->getCollection())
    @php($with3d = $productCollection->filter(fn($p) => $p->resources->firstWhere('resource_type','model_3d')))
    <div class="wc-immersive-board">
        <div class="wc-immersive-main">
            @foreach(($with3d->count() ? $with3d : $productCollection)->take(1) as $product)
                @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
                <a href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}" class="wc-immersive-feature">
                    <div>@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<i class="fa-solid fa-cube"></i>@endif</div>
                    <h2>{!! $product->name !!}</h2><p>Open product details to launch 3D / AR / VR viewer.</p>
                </a>
            @endforeach
        </div>
        <div class="wc-immersive-list">
            @foreach($products as $product)
                @php($thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product))
                @php($flags = \Modules\WebCatalogue\Support\FrontViewHelpers::productFlags($product))
                <a href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, $catalogue ?? null) }}"><span>@if($thumb && $thumb->resolved_url)<img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}">@else<i class="fa-solid fa-cube"></i>@endif</span><strong>{!! $product->name !!}</strong><em>@if($flags['model'])3D @endif @if($flags['ar'])AR @endif @if($flags['vr'])VR @endif</em></a>
            @endforeach
        </div>
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@endif
