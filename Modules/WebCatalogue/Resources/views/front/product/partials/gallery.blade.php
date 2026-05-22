@php
    $productMeta = is_array($product->metadata ?? null) ? $product->metadata : [];
    $finish = in_array(($productMeta['finish'] ?? $productMeta['visual_finish'] ?? 'normal'), ['foil', 'normal'], true)
        ? ($productMeta['finish'] ?? $productMeta['visual_finish'] ?? 'normal')
        : 'normal';
@endphp
<section class="wc-product-gallery-block" data-card-effect="{{ $finish }}">
    <div class="wc-card-finish-toggle" role="group" aria-label="Card finish">
        <button type="button" @class(['is-active' => $finish === 'normal']) data-card-finish="normal">Normal</button>
        <button type="button" @class(['is-active' => $finish === 'foil']) data-card-finish="foil">Foil</button>
    </div>
    <div class="wc-gallery-main wc-no-download" id="galleryMain" oncontextmenu="return false;">
        @if($thumbnail && $thumbnail->resolved_url)
            <div class="wc-parallax-card" data-card-parallax>
                <img src="{{ $thumbnail->resolved_url }}" alt="{{ strip_tags($product->name) }}" id="mainProductImage">
                <span class="wc-card-foil-layer" aria-hidden="true"></span>
                <span class="wc-card-glare-layer" aria-hidden="true"></span>
            </div>
        @else
            <div class="wc-empty"><i class="fa-regular fa-image"></i><br>No product image available.</div>
        @endif
    </div>
    @if($images->count() > 1)
        <div class="wc-gallery-strip">
            @foreach($images as $image)
                @if($image->resolved_url)
                    <img src="{{ $image->resolved_url }}" alt="{{ $image->title ?? strip_tags($product->name) }}" data-gallery-src="{{ $image->resolved_url }}" @class(['is-active' => $loop->first])>
                @endif
            @endforeach
        </div>
    @endif
</section>
