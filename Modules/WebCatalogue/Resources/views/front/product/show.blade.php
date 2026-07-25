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

    const syncFoilBounds = () => {
        if (!main || !parallaxCard || !main.naturalWidth || !main.naturalHeight) return;

        const boxWidth = parallaxCard.clientWidth;
        const boxHeight = parallaxCard.clientHeight;
        if (!boxWidth || !boxHeight) return;

        const imageRatio = main.naturalWidth / main.naturalHeight;
        const boxRatio = boxWidth / boxHeight;
        let renderedWidth;
        let renderedHeight;

        if (boxRatio > imageRatio) {
            renderedHeight = boxHeight;
            renderedWidth = renderedHeight * imageRatio;
        } else {
            renderedWidth = boxWidth;
            renderedHeight = renderedWidth / imageRatio;
        }

        parallaxCard.style.setProperty('--wc-card-img-width', `${renderedWidth}px`);
        parallaxCard.style.setProperty('--wc-card-img-height', `${renderedHeight}px`);
        parallaxCard.style.setProperty('--wc-card-img-left', `${(boxWidth - renderedWidth) / 2}px`);
        parallaxCard.style.setProperty('--wc-card-img-top', `${(boxHeight - renderedHeight) / 2}px`);
    };

    document.querySelectorAll('[data-gallery-src]').forEach(function(img){
        img.addEventListener('click', function(){
            if(main) {
                main.src = this.dataset.gallerySrc;
                requestAnimationFrame(syncFoilBounds);
            }
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
            parallaxCard.style.setProperty('--glare-x', '50%');
            parallaxCard.style.setProperty('--glare-y', '50%');
            parallaxCard.style.setProperty('--foil-angle', '115deg');
            parallaxCard.style.setProperty('--foil-intensity', '.28');
            syncFoilBounds();
        };

        const applyTilt = (clientX, clientY) => {
            const rect = parallaxCard.getBoundingClientRect();
            if(!rect.width || !rect.height) return;
            const x = (clientX - rect.left) / rect.width;
            const y = (clientY - rect.top) / rect.height;
            const rotateY = (x - .5) * 26;
            const rotateX = (.5 - y) * 24;
            const glareX = Math.round((1 - x) * 100);
            const glareY = Math.round((1 - y) * 100);
            const angle = Math.round(110 + ((x - .5) * 34) + ((y - .5) * 18));
            const intensity = Math.min(1, Math.max(0, Math.hypot(x - .5, y - .5) * 2.2));
            parallaxCard.classList.add('is-tilting');
            parallaxCard.style.transform = `perspective(760px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) scale(1.045)`;
            parallaxCard.style.setProperty('--foil-x', `${Math.round(x * 100)}%`);
            parallaxCard.style.setProperty('--foil-y', `${Math.round(y * 100)}%`);
            parallaxCard.style.setProperty('--glare-x', `${glareX}%`);
            parallaxCard.style.setProperty('--glare-y', `${glareY}%`);
            parallaxCard.style.setProperty('--foil-angle', `${angle}deg`);
            parallaxCard.style.setProperty('--foil-intensity', intensity.toFixed(2));
        };

        parallaxCard.addEventListener('pointermove', event => applyTilt(event.clientX, event.clientY));
        parallaxCard.addEventListener('pointerleave', resetTilt);
        parallaxCard.addEventListener('pointercancel', resetTilt);
    }

    main?.addEventListener('load', syncFoilBounds);
    window.addEventListener('resize', syncFoilBounds);
    syncFoilBounds();
})();
</script>
@if(!empty($card3d))
    @include('webcatalogue::front.product.partials.procedural-card-script')
@endif
@endpush
