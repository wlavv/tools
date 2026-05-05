<section class="wc-product-campaign-strip">
    <div><span>Featured product</span><h1>{!! $product->name !!}</h1></div>
    @if($activePrice && $activePrice->regular_price)<strong>{{ number_format((float)($activePrice->sale_price ?: $activePrice->regular_price), 2, ',', ' ') }} {{ $activePrice->currency ?? 'EUR' }}</strong>@endif
</section>
<div class="wc-product-premium-hero wc-card">
    <div>@include('webcatalogue::front.product.partials.gallery')</div>
    <div class="wc-panel">@include('webcatalogue::front.product.partials.summary')</div>
</div>
<div class="wc-product-two-col">@include('webcatalogue::front.product.partials.description') @include('webcatalogue::front.product.partials.resources')</div>
@include('webcatalogue::front.product.partials.viewer')
