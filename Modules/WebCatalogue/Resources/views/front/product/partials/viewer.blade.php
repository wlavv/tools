@if($model3d || !empty($card3d))
<section class="wc-product-viewer-block" id="viewer">
    <div class="wc-product-section-head">
        <div>
            <div class="wc-eyebrow">Interactive experience</div>
            <h2 class="wc-section-title">3D / AR / VR</h2>
        </div>
        <div class="wc-actions">
            <a class="wc-btn wc-btn-primary" href="{{ isset($catalogue) ? route('webcatalogue.front.catalogue.product.viewer', [$store->slug, $catalogue->slug, $product->slug]) : route('webcatalogue.front.product.viewer', [$store->slug, $product->slug]) }}"><i class="fa-solid fa-expand"></i> Immersive viewer</a>
        </div>
    </div>
    <div class="wc-viewer">
        @if($model3d)
        <model-viewer
            src="{{ $model3d->resolved_url }}"
            @if($arFile && $arFile->resolved_url) ios-src="{{ $arFile->resolved_url }}" @endif
            alt="{{ strip_tags($product->name) }}"
            camera-controls
            auto-rotate
            ar
            ar-modes="webxr scene-viewer quick-look"
            shadow-intensity="1"
            exposure="1"
            @if(!empty($environmentPayload['skybox_url']))
                environment-image="{{ $environmentPayload['skybox_url'] }}"
                skybox-image="{{ $environmentPayload['skybox_url'] }}"
            @endif
            interaction-prompt="auto">
        </model-viewer>
        @else
            <div class="wc-procedural-card-viewer"
                data-procedural-card
                data-front-url="{{ $card3d['front_url'] }}"
                data-back-url="{{ $card3d['back_url'] ?? '' }}"
                data-finish="{{ $card3d['finish'] ?? 'normal' }}"
                data-ratio="{{ $card3d['ratio'] ?? 1.395 }}"
                data-thickness="{{ $card3d['thickness'] ?? 0.012 }}"
                data-card-name="{{ strip_tags($product->name) }}"
                data-card-reference="{{ $product->reference }}"
                data-card-category="{{ $product->category }}"
                data-card-description="{{ strip_tags($product->short_description ?: $product->description ?: '') }}"
                data-environment='@json($environmentPayload ?? null)'
                aria-label="{{ strip_tags($product->name) }} 3D card preview">
            </div>
        @endif
    </div>
    <div class="wc-protected-note"><i class="fa-solid fa-shield-halved"></i> {{ $model3d ? '3D/AR/VR files are presented through the viewer and are not exposed as download buttons.' : 'Procedural 3D card generated from product images. The back uses the default card back unless a back image is provided.' }}</div>
</section>
@endif
