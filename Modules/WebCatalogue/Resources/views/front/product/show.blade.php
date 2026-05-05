@extends('webcatalogue::front.layouts.app')

@section('title', strip_tags($product->name ?? 'Product') . ' · ' . ($store->name ?? 'WebCatalogue'))

@section('content')
@php
    $frontMeta = is_array($store->metadata ?? null) ? (($store->metadata['front'] ?? []) ?: []) : [];
    $layoutKey = preg_replace('/[^a-z0-9_\-]/i', '', (string)($frontMeta['layout'] ?? 'classic_catalogue')) ?: 'classic_catalogue';
    $layoutView = 'webcatalogue::front.product.layouts.' . $layoutKey;
    if (!view()->exists($layoutView)) {
        $layoutView = 'webcatalogue::front.product.layouts.classic_catalogue';
    }
@endphp

<div class="wc-front-container wc-product-page wc-product-detail-{{ $layoutKey }}">
    @include($layoutView, [
        'store' => $store,
        'catalogue' => $catalogue ?? null,
        'product' => $product,
        'resources' => $resources,
        'images' => $images,
        'documents' => $documents,
        'videos' => $videos,
        'audio' => $audio,
        'model3d' => $model3d,
        'arFile' => $arFile,
        'vrFile' => $vrFile,
        'thumbnail' => $thumbnail,
        'activePrice' => $activePrice,
    ])
</div>
@endsection

@push('scripts')
<script>
(function(){
    const main = document.getElementById('mainProductImage');
    document.querySelectorAll('[data-gallery-src]').forEach(function(img){
        img.addEventListener('click', function(){
            if(main) main.src = this.dataset.gallerySrc;
            document.querySelectorAll('[data-gallery-src]').forEach(i => i.classList.remove('is-active'));
            this.classList.add('is-active');
        });
    });
})();
</script>
@endpush
