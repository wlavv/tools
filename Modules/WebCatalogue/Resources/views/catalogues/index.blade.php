@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-card">
    <div class="wc-list-toolbar">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-book-open"></i> Publishing</span>
            <h3>Catalogues</h3>
            <p class="wc-muted">Manage showcase, price-list and campaign catalogues.</p>
        </div>
    </div>
    <div class="wc-rich-list">
        @forelse($items as $item)
            @php $cover = $item->coverResource ?? null; $coverUrl = $cover?->resolved_url; @endphp
            <div class="wc-rich-card">
                <div class="wc-rich-media">
                    @if($coverUrl)<img src="{{ $coverUrl }}" alt="{{ $item->name }}">@else<i class="fa-solid fa-book-open"></i>@endif
                </div>
                <div class="wc-rich-body">
                    <div class="wc-rich-title">
                        <h4><a href="{{ route('webcatalogue.catalogues.show', $item) }}">{{ $item->name ?? 'Catalogue #'.$item->id }}</a></h4>
                        <span class="wc-badge wc-status-{{ $item->status ?? 'draft' }}">{{ $item->status ?? 'draft' }}</span>
                    </div>
                    <div class="wc-rich-description">{{ $item->description ?: 'No description defined yet.' }}</div>
                    <div class="wc-rich-meta">
                        <span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $item->store->name ?? 'No store' }}</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-layer-group"></i>{{ $item->catalogue_type ?? 'showcase' }}</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-box"></i>{{ $item->products_count ?? 0 }} products</span>
                        @if($item->show_prices)<span class="wc-rich-metric"><i class="fa-solid fa-tag"></i>Prices visible</span>@endif
                    </div>
                </div>
                <div class="wc-rich-actions">
                    <a class="wc-action-link" href="{{ route('webcatalogue.catalogues.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a>
                    <a class="wc-action-link" href="{{ route('webcatalogue.catalogues.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a>
                </div>
            </div>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-book-open"></i><div><strong>No catalogues yet.</strong><br><span>Create catalogues to publish products by store, campaign or client.</span></div></div>
        @endforelse
    </div>
    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
