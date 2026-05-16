@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@php
    $returnTo = request('return_to');
    $returnQuery = $returnTo ? ['return_to' => $returnTo] : [];
@endphp

<div class="wc-card">
    <div class="wc-list-toolbar">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-box-open"></i> Product layer</span>
            <h3>Products @if(!empty($store)) - {{ $store->name }} @endif</h3>
            <p class="wc-muted">Manage visual ecommerce products, resources, prices and catalogue placement.</p>
        </div>
        <form class="wc-store-search" method="GET" action="{{ route('webcatalogue.products.index') }}">
            @if(!empty($store))<input type="hidden" name="id_store" value="{{ $store->id }}">@endif
            @if($returnTo)<input type="hidden" name="return_to" value="{{ $returnTo }}">@endif
            <div class="wc-field">
                <label>Search product</label>
                <div class="wc-store-search-row">
                    <input name="q" value="{{ request('q') }}" placeholder="Name, reference, SKU, EAN, brand or category">
                    <button class="wc-primary-btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    @if(request()->filled('q'))
                        <a class="wc-secondary-btn" href="{{ route('webcatalogue.products.index', array_merge(!empty($store) ? ['id_store' => $store->id] : [], $returnQuery)) }}"><i class="fa-solid fa-xmark"></i> Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="wc-store-panel-grid">
        @forelse($items as $item)
            @php
                $image = $item->mainImageResource ?? null;
                $imageUrl = $image?->resolved_url;
                $readinessScore = $item->readinessScore();
                $readinessState = $item->readinessState();
                $missingRequired = $item->readinessMissing()->reject(fn($entry) => $entry['optional'] ?? false)->take(3);
            @endphp
            <article class="wc-store-panel">
                <a class="wc-store-panel-main" href="{{ $returnTo ? route('webcatalogue.products.edit', ['product' => $item, 'return_to' => $returnTo]) : route('webcatalogue.products.show', $item) }}">
                    <div class="wc-store-summary-logo">
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ strip_tags($item->name ?? $item->reference) }}">
                        @else
                            <i class="fa-solid fa-box"></i>
                        @endif
                    </div>
                    <div class="wc-store-summary-head">
                        <h4><span class="wc-html-inline">{!! $item->name ?? $item->reference ?? 'Product #'.$item->id !!}</span></h4>
                        <span class="wc-badge wc-status-{{ $item->status ?? 'draft' }}">{{ $item->status ?? 'draft' }}</span>
                    </div>
                </a>

                <div class="wc-store-detail-list">
                    <div class="wc-store-detail-item"><span>Store</span><strong>{{ $item->store->name ?? '-' }}</strong></div>
                    <div class="wc-store-detail-item"><span>Reference</span><strong>{{ $item->reference ?: '-' }}</strong></div>
                    @if($item->brand)<div class="wc-store-detail-item"><span>Brand</span><strong>{{ $item->brand }}</strong></div>@endif
                </div>

                <div class="wc-store-health">
                    <div class="wc-store-health-head">
                        <span>Readiness</span>
                        <strong>{{ $readinessScore }}%</strong>
                    </div>
                    <div class="wc-store-health-track"><div class="wc-store-health-fill is-{{ $readinessState }}" style="width:{{ $readinessScore }}%"></div></div>
                    @if($missingRequired->isNotEmpty())
                        <div class="wc-store-health-missing">
                            @foreach($missingRequired as $missing)
                                <span>{{ $missing['label'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="wc-store-quick-grid">
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.resources.index', array_merge(['id_store' => $item->id_store], $returnQuery)) }}" title="Resources" aria-label="Resources"><i class="fa-solid fa-photo-film"></i><strong>{{ $item->resources_count ?? 0 }}</strong></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.catalogues.index', array_merge(['id_store' => $item->id_store], $returnQuery)) }}" title="Catalogues" aria-label="Catalogues"><i class="fa-solid fa-book-open"></i><strong>{{ $item->catalogues_count ?? 0 }}</strong></a>
                    <span class="wc-store-quick-link" title="Price" aria-label="Price"><i class="fa-solid fa-euro-sign"></i><strong>{{ $item->price !== null ? number_format((float)$item->price, 0, ',', ' ') : '-' }}</strong></span>
                </div>
            </article>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-box-open"></i><div><strong>No products found.</strong><br><span>Create a product manually or import products with CSV resources.</span></div></div>
        @endforelse
    </div>

    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
