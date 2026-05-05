<section class="wc-product-dark-stage">
    <div class="wc-product-dark-viewer">@include('webcatalogue::front.product.partials.viewer') @unless($model3d) @include('webcatalogue::front.product.partials.gallery') @endunless</div>
    <div class="wc-card wc-panel wc-product-dark-info">@include('webcatalogue::front.product.partials.summary')</div>
</section>
<div class="wc-product-two-col">@include('webcatalogue::front.product.partials.description') @include('webcatalogue::front.product.partials.resources')</div>
