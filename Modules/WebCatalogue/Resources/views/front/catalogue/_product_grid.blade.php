@if($products->count())
    <div class="wc-grid">
        @foreach($products as $product)
            @php
                $thumb = $product->resources->firstWhere('is_main', true) ?: $product->resources->first(fn($r) => in_array($r->resource_type, ['image','gallery_image','thumbnail','cover'], true));
                $model = $product->resources->firstWhere('resource_type', 'model_3d');
                $ar = $product->resources->firstWhere('resource_type', 'ar_file');
                $vr = $product->resources->first(fn($r) => in_array($r->resource_type, ['vr_file','vr_scene'], true));
                $video = $product->resources->firstWhere('resource_type', 'video');
                $audio = $product->resources->first(fn($r) => in_array($r->resource_type, ['audio','ambient_audio','voiceover','sound_effect','music_track'], true));
                $price = $product->prices->first();
                $url = !empty($catalogue)
                    ? route('webcatalogue.front.catalogue.product.show', [$store->slug, $catalogue->slug, $product->slug])
                    : route('webcatalogue.front.product.show', [$store->slug, $product->slug]);
            @endphp
            <a class="wc-card wc-product-card" href="{{ $url }}">
                <div class="wc-thumb">
                    @if($thumb && $thumb->resolved_url)
                        <img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}" loading="lazy" draggable="false">
                    @else
                        <div class="wc-thumb-icon"><i class="fa-solid fa-box-open"></i></div>
                    @endif
                    <div class="wc-resource-ribbon">
                        @if($model)<span title="3D model"><i class="fa-solid fa-cube"></i> 3D</span>@endif
                        @if($ar)<span title="AR ready"><i class="fa-solid fa-vr-cardboard"></i> AR</span>@endif
                        @if($vr)<span title="VR ready"><i class="fa-solid fa-headset"></i> VR</span>@endif
                    </div>
                </div>
                <div class="wc-card-body">
                    <h3 class="wc-card-title">{!! $product->name !!}</h3>
                    <div class="wc-card-meta">
                        @if($product->reference)<span class="wc-badge">{{ $product->reference }}</span>@endif
                        @if($product->brand)<span class="wc-badge">{{ $product->brand }}</span>@endif
                        @if($product->category)<span class="wc-badge">{{ $product->category }}</span>@endif
                    </div>
                    <div class="wc-capability-row">
                        <span class="@if($thumb) is-on @endif"><i class="fa-solid fa-image"></i></span>
                        <span class="@if($model) is-on @endif"><i class="fa-solid fa-cube"></i></span>
                        <span class="@if($ar) is-on @endif"><i class="fa-solid fa-vr-cardboard"></i></span>
                        <span class="@if($vr) is-on @endif"><i class="fa-solid fa-headset"></i></span>
                        <span class="@if($video) is-on @endif"><i class="fa-solid fa-video"></i></span>
                        <span class="@if($audio) is-on @endif"><i class="fa-solid fa-volume-high"></i></span>
                    </div>
                    @if($price && $price->regular_price)
                        <div class="wc-price">{{ number_format((float)($price->sale_price ?: $price->regular_price), 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}</div>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
    <div class="wc-pagination">{{ $products->links() }}</div>
@else
    <div class="wc-empty">No products match the current filters.</div>
@endif
