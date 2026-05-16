@extends('webcatalogue::front.layouts.app')

@section('content')
@php
    $capture = $session->captures->firstWhere('capture_type', 'object_photo') ?: $session->captures->first();
    $confidence = $session->matched_score !== null ? (int) round((float) $session->matched_score) : null;
    $confidenceState = $confidence >= 80 ? 'high' : ($confidence >= 55 ? 'medium' : 'low');
    $priceValue = $activePrice?->sale_price ?: $activePrice?->regular_price ?: $product?->price;
    $currency = $activePrice?->currency ?: $product?->currency ?: config('webcatalogue.default_currency', 'EUR');
    $isPublicProduct = $product && in_array((string) $product->status, config('webcatalogue.front_visible_statuses', ['published', 'active']), true);
    $productUrl = ($isPublicProduct && $store) ? route('webcatalogue.front.product.show', [$store->slug, $product->slug]) : null;
    $viewerUrl = ($isPublicProduct && $store && $immersive->isNotEmpty()) ? route('webcatalogue.front.product.viewer', [$store->slug, $product->slug]) : null;
    $resourceGroups = [
        ['title' => 'Manuals and documents', 'icon' => 'fa-file-lines', 'items' => $documents, 'empty' => 'No manuals or technical documents yet.'],
        ['title' => 'Assembly and how-to', 'icon' => 'fa-screwdriver-wrench', 'items' => $assembly, 'empty' => 'No assembly or how-to resources yet.'],
        ['title' => 'Videos and reviews', 'icon' => 'fa-video', 'items' => $videos, 'empty' => 'No videos available yet.'],
        ['title' => '3D / AR / VR', 'icon' => 'fa-cube', 'items' => $immersive, 'empty' => 'No immersive assets available yet.'],
    ];
@endphp

<div class="wc-front-container">
    <section class="wc-scan-result-hero wc-card">
        <div class="wc-scan-result-media">
            @if($thumbnail?->resolved_url)
                <img src="{{ $thumbnail->resolved_url }}" alt="{{ strip_tags($product?->name ?? 'Matched product') }}">
            @elseif($capture?->resolved_url)
                <img src="{{ $capture->resolved_url }}" alt="Scanned object">
            @else
                <i class="fa-solid fa-camera"></i>
            @endif
        </div>

        <div class="wc-scan-result-copy">
            <span class="wc-front-kicker"><i class="fa-solid fa-wand-magic-sparkles"></i> Product intelligence</span>
            @if($product)
                <h1>{!! $product->name !!}</h1>
                <p class="wc-lead">{{ $product->short_description ?: 'Object identified. Review the available information, documentation, media and technical resources.' }}</p>
                <div class="wc-scan-result-tags">
                    <span><i class="fa-solid fa-barcode"></i>{{ $product->reference }}</span>
                    @if($product->brand)<span><i class="fa-solid fa-copyright"></i>{{ $product->brand }}</span>@endif
                    @if($product->category)<span><i class="fa-solid fa-layer-group"></i>{{ $product->category }}</span>@endif
                    @if($confidence !== null)<span class="is-{{ $confidenceState }}"><i class="fa-solid fa-gauge-high"></i>{{ $confidence }}% confidence</span>@endif
                </div>
                <div class="wc-actions">
                    @if($productUrl)<a class="wc-btn wc-btn-primary" href="{{ $productUrl }}"><i class="fa-solid fa-circle-info"></i> Full details</a>@endif
                    @if($viewerUrl)<a class="wc-btn wc-btn-gold" href="{{ $viewerUrl }}"><i class="fa-solid fa-cube"></i> 3D / AR</a>@endif
                </div>
            @else
                <h1>We could not identify this object yet</h1>
                <p class="wc-lead">The scan was saved for review. If you added brand or model details, the team can use them to improve the dataset.</p>
                <div class="wc-actions">
                    <a class="wc-btn wc-btn-primary" href="{{ !empty($isGlobalScan) ? route('webcatalogue.front.scan.global.index') : route('webcatalogue.front.scan.index', $store->slug) }}"><i class="fa-solid fa-camera"></i> Scan again</a>
                    @if($store)<a class="wc-btn" href="{{ route('webcatalogue.front.store.show', $store->slug) }}"><i class="fa-solid fa-store"></i> Explore catalogue</a>@endif
                </div>
            @endif
        </div>
    </section>

    @if($product)
        <section class="wc-scan-intel-grid">
            <div class="wc-card wc-panel">
                <div class="wc-eyebrow">Available knowledge</div>
                <h2>Information found for this object</h2>
                <div class="wc-scan-capability-grid">
                    <a href="#manuals" class="@if($documents->isNotEmpty()) is-on @endif"><i class="fa-solid fa-file-lines"></i><strong>{{ $documents->count() }}</strong><span>Manuals</span></a>
                    <a href="#assembly" class="@if($assembly->isNotEmpty()) is-on @endif"><i class="fa-solid fa-screwdriver-wrench"></i><strong>{{ $assembly->count() }}</strong><span>How-to</span></a>
                    <a href="#videos" class="@if($videos->isNotEmpty()) is-on @endif"><i class="fa-solid fa-video"></i><strong>{{ $videos->count() }}</strong><span>Videos</span></a>
                    <a href="#immersive" class="@if($immersive->isNotEmpty()) is-on @endif"><i class="fa-solid fa-cube"></i><strong>{{ $immersive->count() }}</strong><span>3D / AR</span></a>
                </div>
            </div>

            <aside class="wc-card wc-panel">
                <div class="wc-eyebrow">Source</div>
                <h2>{{ $store?->name ?? 'Source not available' }}</h2>
                <div class="wc-scan-source-box">
                    @if($priceValue)
                        <strong>{{ number_format((float) $priceValue, 2, ',', '.') }} {{ $currency }}</strong>
                        <span>{{ $activePrice?->sale_price ? 'Reference sale price' : 'Reference price' }}</span>
                    @else
                        <strong>Information source</strong>
                        <span>Product details and resources are available from this catalogue source.</span>
                    @endif
                </div>
                <div class="wc-actions">
                    @if($store)<a class="wc-btn wc-btn-primary" href="{{ route('webcatalogue.front.store.show', $store->slug) }}"><i class="fa-solid fa-folder-open"></i> View catalogue</a>@endif
                    @if($purchaseUrl)<a class="wc-btn" href="{{ $purchaseUrl }}" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square"></i> External link</a>@endif
                </div>
            </aside>
        </section>

        <section class="wc-scan-resources">
            @foreach($resourceGroups as $group)
                @php($anchor = str_contains($group['title'], 'Manual') ? 'manuals' : (str_contains($group['title'], 'Assembly') ? 'assembly' : (str_contains($group['title'], 'Videos') ? 'videos' : 'immersive')))
                <div class="wc-card wc-panel" id="{{ $anchor }}">
                    <div class="wc-scan-resource-head">
                        <div><span class="wc-eyebrow"><i class="fa-solid {{ $group['icon'] }}"></i> {{ $group['title'] }}</span><h2>{{ $group['items']->count() }} resource(s)</h2></div>
                    </div>
                    <div class="wc-scan-resource-list">
                        @forelse($group['items'] as $resource)
                            <a class="wc-scan-resource-item" href="{{ $resource->resolved_url ?: '#' }}" @if($resource->resolved_url) target="_blank" rel="noopener" @endif>
                                <i class="{{ $resource->icon }}"></i>
                                <div><strong>{{ $resource->title ?: ucfirst(str_replace('_', ' ', $resource->resource_type)) }}</strong><span>{{ $resource->description ?: $resource->filename ?: $resource->resource_type }}</span></div>
                                <em><i class="fa-solid fa-arrow-up-right-from-square"></i></em>
                            </a>
                        @empty
                            <div class="wc-empty">{{ $group['empty'] }}</div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </section>

        @if($suggestions->isNotEmpty() && $session->status !== 'matched')
            <section class="wc-card wc-panel">
                <div class="wc-eyebrow">Possible alternatives</div>
                <h2>Other close matches</h2>
                <div class="wc-scan-suggestions">
                    @foreach($suggestions as $suggestion)
                        @if($suggestion->product)
                            <a href="{{ route('webcatalogue.front.product.show', [$suggestion->product->store->slug, $suggestion->product->slug]) }}">
                                <strong>{!! $suggestion->product->name !!}</strong>
                                <span>{{ (int) round((float) $suggestion->score) }}% match</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>

<style>
.wc-scan-result-hero{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:26px;align-items:center;margin:18px 0 22px;padding:22px}.wc-scan-result-media{min-height:420px;border-radius:var(--wc-radius);background:var(--wc-image-bg);display:grid;place-items:center;overflow:hidden}.wc-scan-result-media img{width:100%;height:100%;object-fit:contain}.wc-scan-result-media i{font-size:74px;color:var(--wc-muted)}.wc-scan-result-copy h1{font-size:clamp(34px,4vw,58px);line-height:1.02;margin:8px 0 12px}.wc-scan-result-tags{display:flex;gap:8px;flex-wrap:wrap;margin:16px 0}.wc-scan-result-tags span{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--wc-border);border-radius:999px;padding:7px 10px;background:#fff;color:var(--wc-muted);font-weight:850;font-size:12px}.wc-scan-result-tags .is-high{background:#ecfdf5;color:#047857}.wc-scan-result-tags .is-medium{background:#fff7ed;color:#9a3412}.wc-scan-result-tags .is-low{background:#fef2f2;color:#b91c1c}.wc-scan-intel-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;margin-bottom:18px}.wc-scan-capability-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:16px}.wc-scan-capability-grid a{border:1px solid var(--wc-border);border-radius:var(--wc-radius);padding:16px;text-align:center;text-decoration:none;color:var(--wc-muted);background:#fff}.wc-scan-capability-grid a.is-on{color:var(--wc-text);border-color:var(--wc-primary)}.wc-scan-capability-grid i{display:block;font-size:24px;margin-bottom:8px;color:var(--wc-primary)}.wc-scan-capability-grid strong{display:block;font-size:28px;color:var(--wc-text)}.wc-scan-source-box{border:1px solid var(--wc-border);border-radius:var(--wc-radius);padding:16px;background:#fff;margin:14px 0}.wc-scan-source-box strong{display:block;font-size:28px}.wc-scan-source-box span{color:var(--wc-muted);font-size:13px}.wc-scan-resources{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.wc-scan-resource-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:10px}.wc-scan-resource-list{display:grid;gap:10px}.wc-scan-resource-item{display:grid;grid-template-columns:42px minmax(0,1fr) 22px;gap:12px;align-items:center;border:1px solid var(--wc-border);border-radius:var(--wc-radius);padding:12px;background:#fff;text-decoration:none;color:var(--wc-text)}.wc-scan-resource-item>i{width:42px;height:42px;border-radius:var(--wc-radius-sm);display:grid;place-items:center;background:color-mix(in srgb,var(--wc-primary) 12%,#fff);color:var(--wc-primary)}.wc-scan-resource-item strong{display:block}.wc-scan-resource-item span{display:block;color:var(--wc-muted);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.wc-scan-resource-item em{color:var(--wc-muted)}.wc-scan-suggestions{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px}.wc-scan-suggestions a{border:1px solid var(--wc-border);border-radius:var(--wc-radius);padding:12px;text-decoration:none;color:var(--wc-text);background:#fff}.wc-scan-suggestions span{display:block;color:var(--wc-muted);font-size:13px;margin-top:4px}@media(max-width:900px){.wc-scan-result-hero,.wc-scan-intel-grid,.wc-scan-resources{grid-template-columns:1fr}.wc-scan-result-media{min-height:300px}.wc-scan-capability-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>
@endsection
