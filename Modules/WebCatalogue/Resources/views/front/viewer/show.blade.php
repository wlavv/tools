@extends('webcatalogue::front.layouts.app')

@section('title', '3D Viewer - ' . strip_tags($product->name ?? 'Product'))

@push('styles')
<style>
    body{background:#080a10;color:#fff}.wc-topbar,.wc-footer{display:none}.wc-page{padding:0}.wc-front-container{width:100%;max-width:none}.wc-immersive{height:100vh;display:grid;grid-template-columns:1fr 360px;background:#080a10}.wc-immersive-view{position:relative;background:radial-gradient(circle at 50% 20%,#2b3348,#080a10 56%)}.wc-immersive-view model-viewer,.wc-immersive-view .wc-procedural-card-viewer{height:100vh}.wc-immersive-panel{padding:22px;background:rgba(255,255,255,.06);border-left:1px solid rgba(255,255,255,.12);overflow:auto}.wc-immersive-panel h1{font-size:26px;line-height:1.1}.wc-immersive-close{position:absolute;top:18px;left:18px;z-index:5}.wc-immersive-actions{display:grid;gap:10px;margin-top:18px}.wc-immersive-actions a{color:#fff}.wc-mini-gallery{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-top:16px}.wc-mini-gallery img{width:100%;aspect-ratio:1;border-radius:12px;object-fit:cover}.wc-no-model{height:100vh;display:grid;place-items:center;padding:30px;text-align:center}.wc-no-model .wc-card{max-width:520px;color:#111}@media(max-width:900px){.wc-immersive{grid-template-columns:1fr}.wc-immersive-panel{position:absolute;right:12px;bottom:12px;left:12px;max-height:36vh;border-radius:18px;border:1px solid rgba(255,255,255,.12)}}
</style>
@endpush

@section('content')
<div class="wc-front-container">
    @if(($model3d && $model3d->resolved_url) || !empty($card3d))
        <div class="wc-immersive">
            <div class="wc-immersive-view wc-no-download" oncontextmenu="return false;">
                <a class="wc-btn wc-immersive-close" href="{{ isset($catalogue) ? route('webcatalogue.front.catalogue.product.show', [$store->slug, $catalogue->slug, $product->slug]) : route('webcatalogue.front.product.show', [$store->slug, $product->slug]) }}">Back</a>
                @if($model3d && $model3d->resolved_url)
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
                        @else
                            environment-image="neutral"
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
                        data-environment='@json($environmentPayload ?? null)'>
                    </div>
                @endif
            </div>
            <aside class="wc-immersive-panel">
                <div class="wc-eyebrow">Immersive viewer</div>
                <h1>{!! $product->name !!}</h1>
                @if($product->reference)<span class="wc-badge">{{ $product->reference }}</span>@endif
                <div class="wc-immersive-actions">
                    @if($arFile && $arFile->resolved_url)<a class="wc-btn wc-btn-gold" href="{{ $arFile->resolved_url }}" rel="ar">Open AR</a>@endif
                    <span class="wc-btn wc-btn-disabled"><i class="fa-solid fa-shield-halved"></i> {{ $model3d ? 'Protected 3D asset' : 'Procedural 3D card' }}</span>
                </div>
                @if($images->count())
                    <div class="wc-mini-gallery">
                        @foreach($images->take(6) as $image)
                            @if($image->resolved_url)<img src="{{ $image->resolved_url }}" alt="{{ strip_tags($product->name) }}">@endif
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    @else
        <div class="wc-no-model">
            <div class="wc-card wc-panel">
                <h1>No 3D model available</h1>
                <p class="wc-richtext">This product does not have a front image or generated GLB resource yet.</p>
                <a class="wc-btn wc-btn-primary" href="{{ route('webcatalogue.front.product.show', [$store->slug, $product->slug]) }}">Back to product</a>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if(!empty($card3d))
    @include('webcatalogue::front.product.partials.procedural-card-script')
@endif
@endpush
