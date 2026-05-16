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
            <span class="wc-eyebrow"><i class="fa-solid fa-book-open"></i> Publishing</span>
            <h3>Catalogues @if(!empty($store)) - {{ $store->name }} @endif</h3>
            <p class="wc-muted">Manage showcase, price-list and campaign catalogues.</p>
        </div>
        <form class="wc-store-search" method="GET" action="{{ route('webcatalogue.catalogues.index') }}">
            @if(!empty($store))<input type="hidden" name="id_store" value="{{ $store->id }}">@endif
            @if($returnTo)<input type="hidden" name="return_to" value="{{ $returnTo }}">@endif
            <div class="wc-field">
                <label>Search catalogue</label>
                <div class="wc-store-search-row">
                    <input name="q" value="{{ request('q') }}" placeholder="Name, type, visibility or status">
                    <button class="wc-primary-btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    @if(request()->filled('q'))
                        <a class="wc-secondary-btn" href="{{ route('webcatalogue.catalogues.index', array_merge(!empty($store) ? ['id_store' => $store->id] : [], $returnQuery)) }}"><i class="fa-solid fa-xmark"></i> Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="wc-store-panel-grid">
        @forelse($items as $item)
            @php $cover = $item->coverResource ?? null; $coverUrl = $cover?->resolved_url; @endphp
            <article class="wc-store-panel">
                <a class="wc-store-panel-main" href="{{ $returnTo ? route('webcatalogue.catalogues.edit', ['catalogue' => $item, 'return_to' => $returnTo]) : route('webcatalogue.catalogues.show', $item) }}">
                    <div class="wc-store-summary-logo">
                        @if($coverUrl)
                            <img src="{{ $coverUrl }}" alt="{{ $item->name }}">
                        @else
                            <i class="fa-solid fa-book-open"></i>
                        @endif
                    </div>
                    <div class="wc-store-summary-head">
                        <h4>{{ $item->name ?? 'Catalogue #'.$item->id }}</h4>
                        <span class="wc-badge wc-status-{{ $item->status ?? 'draft' }}">{{ $item->status ?? 'draft' }}</span>
                    </div>
                </a>

                <div class="wc-store-detail-list">
                    <div class="wc-store-detail-item"><span>Store</span><strong>{{ $item->store->name ?? '-' }}</strong></div>
                    <div class="wc-store-detail-item"><span>Type</span><strong>{{ $item->catalogue_type ?? 'showcase' }}</strong></div>
                    <div class="wc-store-detail-item"><span>Visibility</span><strong>{{ $item->visibility ?? '-' }}</strong></div>
                </div>

                <div class="wc-store-quick-grid">
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.catalogues.edit', ['catalogue' => $item, 'return_to' => $returnTo]) }}" title="Products" aria-label="Products"><i class="fa-solid fa-boxes-stacked"></i><strong>{{ $item->products_count ?? 0 }}</strong></a>
                    <span class="wc-store-quick-link" title="Prices visible" aria-label="Prices visible"><i class="fa-solid fa-tags"></i><strong>{{ $item->show_prices ? 'On' : 'Off' }}</strong></span>
                    <span class="wc-store-quick-link" title="Promotions visible" aria-label="Promotions visible"><i class="fa-solid fa-bullhorn"></i><strong>{{ $item->show_promotions ? 'On' : 'Off' }}</strong></span>
                </div>
            </article>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-book-open"></i><div><strong>No catalogues found.</strong><br><span>Create catalogues to publish products by store, campaign or client.</span></div></div>
        @endforelse
    </div>

    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
