@if($model3d)
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
            interaction-prompt="auto">
        </model-viewer>
    </div>
    <div class="wc-protected-note"><i class="fa-solid fa-shield-halved"></i> 3D/AR/VR files are presented through the viewer and are not exposed as download buttons.</div>
</section>
@endif
