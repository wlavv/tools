@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-card">
    <div class="wc-list-toolbar">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-store"></i> WebCatalogue</span>
            <h3>Stores</h3>
            <p class="wc-muted">Manage brand/store spaces, assets, catalogues and product ownership.</p>
        </div>
    </div>

    <div class="wc-rich-list">
        @forelse($items as $item)
            @php $logo = $item->logoResource ?? null; $logoUrl = $logo?->resolved_url ?: ($item->logo_path ?? null); @endphp
            <div class="wc-rich-card">
                <div class="wc-rich-media">
                    @if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $item->name }}">@else<i class="fa-solid fa-store"></i>@endif
                </div>
                <div class="wc-rich-body">
                    <div class="wc-rich-title">
                        <h4><a href="{{ route('webcatalogue.stores.show', $item) }}">{{ $item->name ?? 'Store #'.$item->id }}</a></h4>
                        <span class="wc-badge wc-status-{{ $item->status ?? 'draft' }}">{{ $item->status ?? 'draft' }}</span>
                    </div>
                    <div class="wc-rich-description">{{ $item->domain ?: 'No domain defined yet.' }}</div>
                    <div class="wc-rich-meta">
                        <span class="wc-rich-metric"><i class="fa-solid fa-code"></i>{{ $item->code ?: 'No code' }}</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-book-open"></i>{{ $item->catalogues_count ?? 0 }} catalogues</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-box"></i>{{ $item->products_count ?? 0 }} products</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-paperclip"></i>{{ $item->resources_count ?? 0 }} resources</span>
                    </div>
                </div>
                <div class="wc-rich-actions">
                    <a class="wc-action-link" href="{{ route('webcatalogue.stores.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a>
                    <a class="wc-action-link" href="{{ route('webcatalogue.stores.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a>
                </div>
            </div>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-store"></i><div><strong>No stores yet.</strong><br><span>Create the first store to organize catalogues, products and visual identity.</span></div></div>
        @endforelse
    </div>

    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
