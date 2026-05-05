@if(!empty($product->description))
<section class="wc-card wc-panel wc-product-description-block">
    <div class="wc-eyebrow">Product information</div>
    <h2 class="wc-section-title">Description</h2>
    <div class="wc-richtext">{!! $product->description !!}</div>
</section>
@endif
