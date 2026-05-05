<section class="wc-product-immersive-detail">
    @if($model3d)
        @include('webcatalogue::front.product.partials.viewer')
    @else
        @include('webcatalogue::front.product.partials.gallery')
    @endif
    <div class="wc-card wc-panel">@include('webcatalogue::front.product.partials.summary')</div>
</section>
<div class="wc-product-two-col">@include('webcatalogue::front.product.partials.description') @include('webcatalogue::front.product.partials.resources')</div>
