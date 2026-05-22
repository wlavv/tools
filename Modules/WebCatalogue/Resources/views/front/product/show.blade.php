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
    const galleryBlock = document.querySelector('.wc-product-gallery-block[data-card-effect]');
    const parallaxCard = document.querySelector('[data-card-parallax]');

    document.querySelectorAll('[data-gallery-src]').forEach(function(img){
        img.addEventListener('click', function(){
            if(main) main.src = this.dataset.gallerySrc;
            document.querySelectorAll('[data-gallery-src]').forEach(i => i.classList.remove('is-active'));
            this.classList.add('is-active');
        });
    });

    if(galleryBlock){
        galleryBlock.querySelectorAll('[data-card-finish]').forEach(function(button){
            button.addEventListener('click', function(){
                galleryBlock.dataset.cardEffect = this.dataset.cardFinish || 'normal';
                galleryBlock.querySelectorAll('[data-card-finish]').forEach(item => item.classList.remove('is-active'));
                this.classList.add('is-active');
            });
        });
    }

    if(parallaxCard){
        const resetTilt = () => {
            parallaxCard.classList.remove('is-tilting');
            parallaxCard.style.transform = '';
            parallaxCard.style.setProperty('--foil-x', '50%');
            parallaxCard.style.setProperty('--foil-y', '50%');
        };

        const applyTilt = (clientX, clientY) => {
            const rect = parallaxCard.getBoundingClientRect();
            if(!rect.width || !rect.height) return;
            const x = (clientX - rect.left) / rect.width;
            const y = (clientY - rect.top) / rect.height;
            const rotateY = (x - .5) * 18;
            const rotateX = (.5 - y) * 18;
            parallaxCard.classList.add('is-tilting');
            parallaxCard.style.transform = `perspective(900px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale(1.025)`;
            parallaxCard.style.setProperty('--foil-x', `${Math.round(x * 100)}%`);
            parallaxCard.style.setProperty('--foil-y', `${Math.round(y * 100)}%`);
        };

        parallaxCard.addEventListener('pointermove', event => applyTilt(event.clientX, event.clientY));
        parallaxCard.addEventListener('pointerleave', resetTilt);
        parallaxCard.addEventListener('pointercancel', resetTilt);
    }
})();
</script>
@if(!empty($card3d))
    @include('webcatalogue::front.product.partials.procedural-card-script')
@endif
@endpush
