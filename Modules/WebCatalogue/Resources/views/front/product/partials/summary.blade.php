<div class="wc-product-summary-block" id="details">
    <div class="wc-eyebrow">{{ $product->brand ?? $product->category ?? 'Product' }}</div>
    <h1>{!! $product->name !!}</h1>
    <div class="wc-card-meta">
        @if($product->reference)<span class="wc-badge">Ref: {{ $product->reference }}</span>@endif
        @if($product->sku)<span class="wc-badge">SKU: {{ $product->sku }}</span>@endif
        @if($product->ean13)<span class="wc-badge">EAN: {{ $product->ean13 }}</span>@endif
        @if($product->brand)<span class="wc-badge"><i class="fa-solid fa-copyright"></i> {{ $product->brand }}</span>@endif
        @if($product->category)<span class="wc-badge"><i class="fa-solid fa-layer-group"></i> {{ $product->category }}</span>@endif
    </div>
    <div class="wc-resource-status">
        <span class="@if($images->count()) is-on @endif"><i class="fa-solid fa-image"></i> Images</span>
        <span class="@if($model3d) is-on @endif"><i class="fa-solid fa-cube"></i> 3D</span>
        <span class="@if($arFile) is-on @endif"><i class="fa-solid fa-vr-cardboard"></i> AR</span>
        <span class="@if($vrFile) is-on @endif"><i class="fa-solid fa-headset"></i> VR</span>
        <span class="@if($videos->count()) is-on @endif"><i class="fa-solid fa-video"></i> Video</span>
        <span class="@if($documents->count()) is-on @endif"><i class="fa-solid fa-file-lines"></i> Docs</span>
    </div>
    <div class="wc-purchase-row">
        @if($activePrice && $activePrice->regular_price)
            <div class="wc-price">{{ number_format((float)($activePrice->sale_price ?: $activePrice->regular_price), 2, ',', ' ') }} {{ $activePrice->currency ?? 'EUR' }}</div>
        @endif
        <button class="wc-btn wc-btn-gold" type="button"><i class="fa-solid fa-cart-shopping"></i> Adicionar ao carrinho</button>
    </div>
    @if(!empty($product->short_description))
        <div class="wc-richtext wc-short-description">{!! $product->short_description !!}</div>
    @endif
    <div class="wc-actions">
        @if($model3d)<a class="wc-btn wc-btn-primary" href="#viewer"><i class="fa-solid fa-cube"></i> View 3D</a>@endif
        @if($arFile && $arFile->resolved_url)<a class="wc-btn wc-btn-gold" href="{{ $arFile->resolved_url }}" rel="ar"><i class="fa-solid fa-vr-cardboard"></i> AR</a>@endif
        @if($vrFile || $model3d)<a class="wc-btn" href="{{ isset($catalogue) ? route('webcatalogue.front.catalogue.product.viewer', [$store->slug, $catalogue->slug, $product->slug]) : route('webcatalogue.front.product.viewer', [$store->slug, $product->slug]) }}"><i class="fa-solid fa-expand"></i> Fullscreen</a>@endif
    </div>
</div>
