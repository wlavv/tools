<section class="wc-product-gallery-block">
    <div class="wc-gallery-main wc-no-download" id="galleryMain" oncontextmenu="return false;">
        @if($thumbnail && $thumbnail->resolved_url)
            <img src="{{ $thumbnail->resolved_url }}" alt="{{ strip_tags($product->name) }}" id="mainProductImage">
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
