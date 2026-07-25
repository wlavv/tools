@php
    $productMeta = is_array($product->metadata ?? null) ? $product->metadata : [];
    $detailRows = collect([
        'Set' => $productMeta['set_name'] ?? null,
        'Numero' => !empty($productMeta['collector_number']) ? '#' . $productMeta['collector_number'] : null,
        'Raridade' => !empty($productMeta['rarity']) ? ucfirst((string) $productMeta['rarity']) : null,
        'Tipo' => $productMeta['type_line'] ?? $product->category ?? null,
        'Artista' => $productMeta['artist'] ?? null,
        'Referencia' => $product->reference ?? null,
    ])->filter(fn ($value) => trim((string) $value) !== '');
@endphp

<div class="wc-product-storefront-detail">
    <section class="wc-product-storefront-media" aria-label="Product gallery">
        @include('webcatalogue::front.product.partials.gallery', ['showSingleThumb' => true])
    </section>

    <aside class="wc-card wc-panel wc-product-storefront-info">
        @include('webcatalogue::front.product.partials.summary')

        @if(!empty($product->description))
            <section class="wc-product-side-section">
                <div class="wc-eyebrow">Descricao</div>
                <h2>Informacao do produto</h2>
                <div class="wc-richtext">{!! $product->description !!}</div>
            </section>
        @endif

        @if($detailRows->count())
            <section class="wc-product-side-section">
                <div class="wc-eyebrow">Detalhes</div>
                <dl class="wc-product-detail-list">
                    @foreach($detailRows as $label => $value)
                        <div>
                            <dt>{{ $label }}</dt>
                            <dd>{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        @include('webcatalogue::front.product.partials.resources', ['embeddedResources' => true])

    </aside>
</div>

<div class="wc-product-full-viewer">
    @include('webcatalogue::front.product.partials.viewer')
</div>

@push('styles')
<style>
.wc-product-detail-visual_gallery{width:100%;max-width:none;padding-inline:24px}
.wc-product-storefront-detail{display:grid;grid-template-columns:minmax(0,.85fr) minmax(600px,1.08fr);gap:20px;align-items:stretch}
.wc-product-storefront-media{min-width:0;display:flex;flex-direction:column;height:100%}
.wc-product-storefront-info{display:flex;flex-direction:column;gap:12px;height:100%}
.wc-product-storefront-info.wc-panel{padding:16px}
.wc-product-detail-visual_gallery .wc-product-gallery-block{display:grid;grid-template-columns:88px minmax(0,1fr);grid-template-rows:minmax(0,1fr);grid-template-areas:"thumbs main";gap:12px;align-items:stretch;flex:1 1 auto;min-height:min(50vh,500px)}
.wc-product-detail-visual_gallery .wc-card-finish-toggle{position:absolute;z-index:6;top:14px;right:14px;margin:0;background:rgba(255,255,255,.86);border-color:rgba(15,23,42,.12);box-shadow:0 10px 26px rgba(15,23,42,.16);backdrop-filter:blur(10px)}
.wc-product-detail-visual_gallery .wc-card-finish-toggle button{padding:6px 10px;font-size:11px}
.wc-product-detail-visual_gallery .wc-gallery-main{grid-area:main;height:100%;max-height:none;padding:18px}
.wc-product-detail-visual_gallery .wc-gallery-strip{grid-area:thumbs;display:grid;grid-template-columns:1fr;align-content:start;gap:10px;margin:0;max-height:none;overflow:auto;padding-right:2px}
.wc-product-detail-visual_gallery .wc-gallery-strip img{width:100%;aspect-ratio:.72;border-radius:var(--wc-radius-sm);background:var(--wc-image-bg);object-fit:contain;padding:5px}
.wc-product-detail-visual_gallery .wc-gallery-strip img.is-active{border-color:var(--wc-primary);box-shadow:0 0 0 2px color-mix(in srgb,var(--wc-primary) 18%,transparent)}
.wc-product-detail-visual_gallery .wc-parallax-card{width:100%;height:100%}
.wc-product-detail-visual_gallery .wc-parallax-card img{max-height:88%;max-width:88%}
.wc-product-storefront-info .wc-product-summary-block h1{font-size:clamp(28px,2.45vw,40px);margin-bottom:8px}
.wc-product-storefront-info .wc-card-meta,.wc-product-storefront-info .wc-resource-status{gap:6px;margin-top:6px}
.wc-product-storefront-info .wc-badge,.wc-product-storefront-info .wc-resource-status span{padding:5px 8px;font-size:11px}
.wc-product-storefront-info .wc-purchase-row{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:10px}
.wc-product-storefront-info .wc-purchase-row .wc-btn{flex:0 0 auto;margin-left:auto;white-space:nowrap}
.wc-product-storefront-info .wc-price{margin-top:0;font-size:22px}
.wc-product-storefront-info .wc-short-description{margin-top:10px;line-height:1.55}
.wc-product-storefront-info .wc-actions{display:none}
.wc-product-side-section{border-top:1px solid var(--wc-border);padding-top:12px}
.wc-product-side-section h2{margin:0 0 9px;font-size:18px;line-height:1.15}
.wc-product-detail-list{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin:0}
.wc-product-detail-list div{min-width:0;border:1px solid var(--wc-border);border-radius:var(--wc-radius-sm);background:color-mix(in srgb,var(--wc-surface) 88%,var(--wc-primary-2) 12%);padding:9px}
.wc-product-detail-list dt{margin:0 0 4px;color:var(--wc-muted);font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}
.wc-product-detail-list dd{margin:0;color:var(--wc-text);font-weight:850;line-height:1.25}
.wc-product-detail-visual_gallery .wc-product-resources-block-embedded{border-top:0;padding-top:0}
.wc-product-detail-visual_gallery .wc-product-resources-block-embedded .wc-section-title{margin:0 0 10px;font-size:18px;line-height:1.15}
.wc-product-detail-visual_gallery .wc-product-resources-block-embedded .wc-product-resource-summary{grid-template-columns:repeat(4,minmax(0,1fr))}
.wc-product-full-viewer{width:100%;margin-top:14px}
.wc-product-detail-visual_gallery .wc-product-viewer-block{width:100%;margin-top:0}
.wc-product-detail-visual_gallery .wc-product-section-head{margin:0 0 12px}
.wc-product-detail-visual_gallery .wc-viewer{height:min(82vh,840px);min-height:620px}
@media(max-width:1180px){.wc-product-storefront-detail{grid-template-columns:1fr;align-items:start}.wc-product-storefront-info{position:static}.wc-product-detail-visual_gallery .wc-product-gallery-block{min-height:min(58vh,500px)}}
@media(max-width:720px){.wc-product-detail-visual_gallery{padding-inline:12px}.wc-product-detail-visual_gallery .wc-product-gallery-block{grid-template-columns:74px minmax(0,1fr);gap:9px;min-height:min(58vh,460px)}.wc-product-detail-visual_gallery .wc-gallery-main{padding:12px}.wc-product-detail-list{grid-template-columns:repeat(2,minmax(0,1fr))}.wc-product-detail-visual_gallery .wc-product-resources-block-embedded .wc-product-resource-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.wc-product-detail-visual_gallery .wc-viewer{min-height:460px}}
@media(max-width:520px){.wc-product-detail-visual_gallery .wc-product-gallery-block{grid-template-columns:1fr;grid-template-rows:auto auto;grid-template-areas:"main" "thumbs";min-height:0}.wc-product-detail-visual_gallery .wc-gallery-main{height:min(58vh,440px)}.wc-product-detail-visual_gallery .wc-card-finish-toggle{top:10px;right:10px}.wc-product-detail-visual_gallery .wc-gallery-strip{display:flex;overflow:auto;max-height:none;padding:0 0 4px}.wc-product-detail-visual_gallery .wc-gallery-strip img{width:72px;flex:0 0 72px}.wc-product-detail-list{grid-template-columns:1fr}.wc-product-storefront-info .wc-purchase-row{align-items:stretch;flex-direction:column}.wc-product-storefront-info .wc-actions .wc-btn{width:100%}}
</style>
@endpush
