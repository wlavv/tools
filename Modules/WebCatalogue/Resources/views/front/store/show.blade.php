@extends('webcatalogue::front.layouts.app')

@section('title', ($store->name ?? 'Store') . ' - WebCatalogue')
@section('body_class', 'wc-store-front-page')

@section('content')
@php
    $front = is_array($store->metadata ?? null) ? (($store->metadata['front'] ?? []) ?: []) : [];
    $layoutKey = preg_replace('/[^a-z0-9_\-]/i', '', (string)($front['layout'] ?? 'classic_catalogue')) ?: 'classic_catalogue';
    $scanUrl = route('webcatalogue.front.scan.index', $store->slug);
    $productsUrl = route('webcatalogue.front.store.products', $store->slug);
    $catalogueSets = $catalogues->map(function ($catalogue) {
        $metadata = is_array($catalogue->metadata ?? null) ? $catalogue->metadata : [];
        $code = strtoupper((string) ($metadata['set_code'] ?? preg_replace('/^mtg-/i', '', (string) $catalogue->slug)));

        return [
            'name' => (string) ($metadata['set_name'] ?? preg_replace('/^MTG\s*-\s*/i', '', (string) $catalogue->name)),
            'code' => $code,
            'game' => (string) ($metadata['game'] ?? ''),
            'card_count' => (int) ($metadata['card_count'] ?? 0),
            'released_at' => (string) ($metadata['released_at'] ?? ''),
            'icon_svg_uri' => (string) ($metadata['icon_svg_uri'] ?? ''),
        ];
    })->filter(fn ($set) => $set['name'] !== '' || $set['code'] !== '')->unique(fn ($set) => $set['code'] ?: $set['name'])->values();
    $isTcgStore = $store->slug === 'tcg-collectors' || $catalogueSets->contains(fn ($set) => str_contains(strtolower($set['game']), 'magic'));
    $latestCatalogue = $catalogues->sortByDesc(function ($catalogue) {
        $metadata = is_array($catalogue->metadata ?? null) ? $catalogue->metadata : [];
        return (string) ($metadata['released_at'] ?? '');
    })->first();
    $latestCatalogueUrl = $latestCatalogue ? route('webcatalogue.front.catalogue.show', [$store->slug, $latestCatalogue->slug]) : $productsUrl;
    $storeBanners = [
        [
            'image' => asset('modules/webcatalogue/images/tcg-home-banner-card-month.webp'),
            'url' => $productsUrl,
            'alt' => 'Carta TCG em destaque na loja',
        ],
        [
            'image' => asset('modules/webcatalogue/images/tcg-home-banner-new-set.webp'),
            'url' => $latestCatalogueUrl,
            'alt' => 'Novo set TCG em destaque',
        ],
        [
            'image' => asset('modules/webcatalogue/images/tcg-home-banner-event.webp'),
            'url' => $productsUrl,
            'alt' => 'Evento TCG na loja',
        ],
    ];
    $homeProducts = $featuredProducts->take(6);
@endphp
<div class="wc-front-container wc-store-front-container">
    @if(!empty($isPreview))
        <div class="wc-front-preview-banner">
            <strong>Preview</strong>
            <span>This catalogue is protected by a preview token and is not the public published link.</span>
        </div>
    @endif

    <section class="wc-hero wc-store-hero wc-layout-hero wc-layout-hero-{{ $layoutKey }}">
        <div class="wc-hero-card wc-store-hero-copy">
            <div class="wc-store-slider" data-store-slider>
                <div class="wc-store-slider-track">
                    @foreach($storeBanners as $banner)
                        <article class="wc-store-slide {{ $loop->first ? 'is-active' : '' }}" data-store-slide>
                            <a class="wc-store-slide-image" href="{{ $banner['url'] }}">
                                <img src="{{ $banner['image'] }}" alt="{{ $banner['alt'] }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" draggable="false">
                            </a>
                        </article>
                    @endforeach
                </div>
                <div class="wc-store-slider-controls" aria-label="Destaques da loja">
                    <button type="button" data-store-slide-prev aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>
                    <div class="wc-store-slider-dots">
                        @for($slideIndex = 0; $slideIndex < count($storeBanners); $slideIndex++)
                            <button type="button" class="{{ $slideIndex === 0 ? 'is-active' : '' }}" data-store-slide-dot="{{ $slideIndex }}" aria-label="Slide {{ $slideIndex + 1 }}"></button>
                        @endfor
                    </div>
                    <button type="button" data-store-slide-next aria-label="Seguinte"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

        <aside class="wc-card wc-scan-feature-card" aria-label="Scan card">
            <div class="wc-scan-feature-copy">
                <div class="wc-eyebrow">{{ $isTcgStore ? 'Integracao na loja' : 'Main tool' }}</div>
                <h2>{{ $isTcgStore ? 'Encontrar por imagem, abrir o produto certo' : 'Scan a product' }}</h2>
                <p>{{ $isTcgStore ? 'Na simulacao de loja, o scan ajuda o cliente que tem a carta na mao mas nao sabe o nome, set ou numero exato.' : 'Open the camera, lock focus and identify products against this catalogue.' }}</p>
            </div>
            <figure class="wc-scan-explainer">
                <img src="{{ asset('modules/webcatalogue/images/tcg-scan-explainer.webp') }}" alt="Scanner de cartas: fotografa a carta, reconhece set e numero, abre a ficha de produto com preco e compra.">
            </figure>
            <a class="wc-btn wc-btn-gold wc-scan-feature-action" href="{{ $scanUrl }}"><i class="fa-solid fa-camera"></i> {{ $isTcgStore ? 'Procurar por scan' : 'Open scan' }}</a>
        </aside>
    </section>

    @if($catalogues->count())
        <section class="wc-home-section wc-home-sets" aria-label="Quick sets">
            <div class="wc-home-section-head">
                <div>
                    <div class="wc-eyebrow">{{ $isTcgStore ? 'Sets' : 'Catalogues' }}</div>
                    <h2>{{ $isTcgStore ? 'Explorar por set' : 'Explore collections' }}</h2>
                </div>
            </div>
            <div class="wc-home-set-grid">
                @foreach($catalogues->take(8) as $catalogue)
                    @php
                        $catalogueMeta = is_array($catalogue->metadata ?? null) ? $catalogue->metadata : [];
                        $catalogueSetCode = strtoupper((string) ($catalogueMeta['set_code'] ?? preg_replace('/^mtg-/i', '', (string) $catalogue->slug)));
                        $catalogueName = (string) ($catalogueMeta['set_name'] ?? preg_replace('/^MTG\s*-\s*/i', '', (string) $catalogue->name));
                        $catalogueIcon = trim((string) ($catalogueMeta['icon_svg_uri'] ?? ''));
                        $localSetLogoPath = 'images/mtg/' . strtolower($catalogueSetCode) . '/logo/' . strtolower($catalogueSetCode) . '.svg';
                        if ($catalogueIcon === '' && file_exists(public_path($localSetLogoPath))) {
                            $catalogueIcon = asset($localSetLogoPath);
                        }
                        $catalogueInfo = trim(implode(' - ', array_filter([
                            !empty($catalogueMeta['card_count']) ? $catalogueMeta['card_count'] . ' cards' : null,
                            $catalogueMeta['released_at'] ?? null,
                        ])));
                    @endphp
                    <a class="wc-home-set-link" href="{{ route('webcatalogue.front.catalogue.show', [$store->slug, $catalogue->slug]) }}">
                        <span class="wc-home-set-logo" aria-hidden="true">
                            @if($catalogueIcon !== '')
                                <img src="{{ $catalogueIcon }}" alt="" loading="lazy" draggable="false">
                            @else
                                <strong>{{ $isTcgStore ? $catalogueSetCode : mb_substr((string) $catalogue->name, 0, 2) }}</strong>
                            @endif
                        </span>
                        <span class="wc-home-set-copy">
                            <strong>{{ $isTcgStore ? $catalogueSetCode : $catalogue->name }}</strong>
                            <span>{{ $catalogueName }}</span>
                            @if($catalogueInfo !== '')<small>{{ $catalogueInfo }}</small>@endif
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if($homeProducts->count())
        <section class="wc-home-section wc-home-products" aria-label="Featured products">
            <div class="wc-home-section-head">
                <div>
                    <div class="wc-eyebrow">{{ $isTcgStore ? 'Montra' : 'Products' }}</div>
                    <h2>{{ $isTcgStore ? 'Cartas em destaque' : 'Featured products' }}</h2>
                </div>
                <a class="wc-btn" href="{{ $productsUrl }}"><i class="fa-solid fa-border-all"></i> {{ $isTcgStore ? 'Ver todas' : 'View all' }}</a>
            </div>
            <div class="wc-home-product-grid">
                @foreach($homeProducts as $product)
                    @php
                        $thumb = \Modules\WebCatalogue\Support\FrontViewHelpers::productThumb($product);
                        $price = \Modules\WebCatalogue\Support\FrontViewHelpers::productPrice($product);
                        $priceValue = $price && ($price->regular_price || $price->sale_price) ? ($price->sale_price ?: $price->regular_price) : ($product->price ?? null);
                        $productMeta = is_array($product->metadata ?? null) ? $product->metadata : [];
                        $productInfo = trim(implode(' - ', array_filter([
                            strtoupper((string) ($productMeta['set_code'] ?? '')),
                            !empty($productMeta['collector_number']) ? '#' . $productMeta['collector_number'] : null,
                        ])));
                    @endphp
                    <article class="wc-home-product-card">
                        <a class="wc-home-product-link" href="{{ \Modules\WebCatalogue\Support\FrontViewHelpers::productUrl($product, $store, null) }}">
                            <span class="wc-home-product-media">
                                @if($thumb && $thumb->resolved_url)
                                    <img src="{{ $thumb->resolved_url }}" alt="{{ strip_tags($product->name) }}" loading="lazy" draggable="false">
                                @else
                                    <i class="fa-solid fa-image"></i>
                                @endif
                            </span>
                            <strong>{!! $product->name !!}</strong>
                            <small>{{ $productInfo ?: ($product->category ?? 'Produto') }}</small>
                        </a>
                        <div class="wc-home-product-commerce">
                            @if($priceValue)
                                <em>{{ number_format((float) $priceValue, 2, ',', ' ') }} {{ $price->currency ?? 'EUR' }}</em>
                            @endif
                            <button class="wc-demo-buy-button" type="button"><i class="fa-solid fa-cart-shopping"></i> Comprar</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection

@push('styles')
<style>
.wc-store-front-page .wc-front-container{width:100%;max-width:none;padding-inline:24px}
.wc-hero.wc-store-hero{grid-template-columns:minmax(0,1.15fr) minmax(420px,.62fr);gap:18px;align-items:stretch}
.wc-store-hero-copy{display:flex;flex-direction:column;justify-content:flex-start;height:100%;padding:0}
.wc-store-slider{position:relative;height:100%;border:0;border-radius:0;background:color-mix(in srgb,var(--wc-surface) 92%,transparent);overflow:hidden}
.wc-store-slider-track{position:relative;min-height:360px;height:100%}
.wc-store-slide{display:none;grid-template-columns:1fr;align-items:stretch;min-height:360px;height:100%;padding:0}
.wc-store-slide.is-active{display:grid}
.wc-store-slide-image{display:grid;place-items:center;min-height:360px;height:100%;border-radius:0;background:linear-gradient(135deg,var(--wc-image-bg),color-mix(in srgb,var(--wc-primary-2) 16%,var(--wc-image-bg)));overflow:hidden}
.wc-store-slide-image img{display:block;width:100%;height:100%;max-height:none;object-fit:cover;object-position:left center;padding:0}
.wc-store-slide-image i{font-size:44px;color:var(--wc-primary)}
.wc-store-slider-controls{position:absolute;left:12px;right:12px;bottom:12px;z-index:2;display:flex;align-items:center;justify-content:space-between;gap:10px;padding:0;pointer-events:none}
.wc-store-slider-controls button{border:1px solid rgba(255,255,255,.45);border-radius:999px;background:rgba(14,19,33,.72);color:#fff;width:34px;height:34px;display:grid;place-items:center;cursor:pointer;box-shadow:0 12px 28px rgba(0,0,0,.24);pointer-events:auto}
.wc-store-slider-dots{display:flex;gap:6px;padding:8px 10px;border-radius:999px;background:rgba(14,19,33,.62);box-shadow:0 12px 28px rgba(0,0,0,.2);pointer-events:auto}.wc-store-slider-dots button{width:9px;height:9px;padding:0;border:0;border-radius:999px;background:rgba(255,255,255,.58);box-shadow:none}.wc-store-slider-dots button.is-active{width:24px;background:#fff}
.wc-scan-feature-card{display:flex;flex-direction:column;gap:10px;height:100%;padding:14px;background:linear-gradient(180deg,color-mix(in srgb,var(--wc-surface) 94%,var(--wc-primary) 6%),var(--wc-surface));overflow:hidden}
.wc-scan-feature-copy .wc-eyebrow{margin-bottom:6px}
.wc-scan-feature-copy h2{font-size:clamp(23px,2.2vw,30px);line-height:1;margin:0 0 7px}
.wc-scan-feature-copy p{margin:0;color:var(--wc-muted);line-height:1.42;font-size:14px}
.wc-scan-explainer{margin:0;border:1px solid var(--wc-border);border-radius:var(--wc-radius);background:#111827;box-shadow:inset 0 0 0 1px rgba(255,255,255,.05),0 22px 50px rgba(15,23,42,.16);overflow:hidden}
.wc-scan-explainer img{display:block;width:100%;height:auto}
.wc-scan-feature-action{width:100%;margin-top:auto}
.wc-home-section{margin-top:18px}
.wc-home-section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:12px}
.wc-home-section-head .wc-eyebrow{margin-bottom:5px}
.wc-home-section-head h2{margin:0;font-size:22px;line-height:1.1}
.wc-home-set-grid{display:grid;grid-template-columns:repeat(8,minmax(0,1fr));gap:10px}
.wc-home-set-link{position:relative;display:grid;grid-template-rows:78px minmax(0,1fr);gap:9px;min-height:158px;border:1px solid var(--wc-border);border-radius:var(--wc-radius);background:linear-gradient(180deg,color-mix(in srgb,var(--wc-surface) 92%,#f2c94c 8%),var(--wc-surface));box-shadow:var(--wc-shadow);padding:10px;overflow:hidden}
.wc-home-set-link::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 50% 0,color-mix(in srgb,var(--wc-primary) 14%,transparent),transparent 54%);opacity:.9;pointer-events:none}
.wc-home-set-logo{position:relative;z-index:1;display:grid;place-items:center;min-width:0;border:1px solid color-mix(in srgb,var(--wc-border) 72%,transparent);border-radius:var(--wc-radius-sm);background:rgba(255,255,255,.72);box-shadow:inset 0 0 0 1px rgba(255,255,255,.45)}
.wc-home-set-logo img{display:block;max-width:52px;max-height:52px;object-fit:contain;filter:drop-shadow(0 8px 12px rgba(15,23,42,.12))}
.wc-home-set-logo strong{font-size:20px;line-height:1;color:var(--wc-primary);font-weight:950}
.wc-home-set-copy{position:relative;z-index:1;display:flex;min-width:0;flex-direction:column;gap:4px}
.wc-home-set-copy strong{font-size:20px;line-height:1;color:var(--wc-primary)}
.wc-home-set-copy span{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:13px;font-weight:900;line-height:1.2;color:var(--wc-text)}
.wc-home-set-copy small{margin-top:auto;color:var(--wc-muted);font-size:11px;font-weight:780;line-height:1.3}
.wc-home-product-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px}
.wc-home-product-card{display:flex;min-width:0;flex-direction:column;border:1px solid var(--wc-border);border-radius:var(--wc-radius);background:var(--wc-surface);box-shadow:var(--wc-shadow);overflow:hidden}
.wc-home-product-link{display:flex;min-width:0;flex-direction:column;color:inherit}
.wc-home-product-media{display:grid;place-items:center;aspect-ratio:.72;background:var(--wc-image-bg);padding:7px}
.wc-home-product-media img{display:block;width:100%;height:100%;object-fit:contain}
.wc-home-product-media i{font-size:34px;color:var(--wc-muted)}
.wc-home-product-link strong{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin:10px 10px 4px;font-size:14px;line-height:1.2}
.wc-home-product-link small{margin:0 10px;color:var(--wc-muted);font-size:11px;font-weight:800;line-height:1.35}
.wc-home-product-commerce{display:flex;align-items:center;justify-content:space-between;gap:8px;margin:auto 10px 10px;border-top:1px solid var(--wc-border);padding-top:9px}
.wc-home-product-commerce em{min-width:0;color:var(--wc-text);font-style:normal;font-size:13px;font-weight:950}
.wc-demo-buy-button{display:inline-flex;align-items:center;justify-content:center;gap:6px;border:0;border-radius:var(--wc-radius-sm);background:var(--wc-dark);color:#fff;padding:7px 9px;font:inherit;font-size:11px;font-weight:950;white-space:nowrap;cursor:default}
.wc-layout-dark_immersive .wc-scan-feature-card,.wc-layout-immersive_3d .wc-scan-feature-card{background:rgba(17,24,39,.94);border-color:rgba(255,255,255,.12)}
@media(max-width:980px){.wc-hero.wc-store-hero{grid-template-columns:1fr}}
@media(max-width:1180px){.wc-home-set-grid{grid-template-columns:repeat(4,minmax(0,1fr))}.wc-home-product-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media(max-width:900px){.wc-home-product-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:760px){.wc-home-section-head{align-items:flex-start;flex-direction:column}.wc-home-set-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.wc-home-product-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:640px){.wc-store-front-page .wc-front-container{padding-inline:12px}.wc-store-slide,.wc-store-slide-image{min-height:240px}.wc-store-slider-controls{left:10px;right:10px;bottom:10px}}
@media(max-width:430px){.wc-home-set-grid,.wc-home-product-grid{grid-template-columns:1fr}}
</style>
@endpush

@push('scripts')
<script>
document.querySelectorAll('[data-store-slider]').forEach((slider) => {
    const slides = Array.from(slider.querySelectorAll('[data-store-slide]'));
    const dots = Array.from(slider.querySelectorAll('[data-store-slide-dot]'));
    const prev = slider.querySelector('[data-store-slide-prev]');
    const next = slider.querySelector('[data-store-slide-next]');
    let active = 0;

    const show = (index) => {
        if (!slides.length) return;
        active = (index + slides.length) % slides.length;
        slides.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === active));
        dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === active));
    };

    prev?.addEventListener('click', () => show(active - 1));
    next?.addEventListener('click', () => show(active + 1));
    dots.forEach((dot) => dot.addEventListener('click', () => show(Number(dot.dataset.storeSlideDot || 0))));
    show(0);
});
</script>
@endpush
