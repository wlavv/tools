@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-card">
    <div class="wc-list-toolbar">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-box-open"></i> Product layer</span>
            <h3>Products</h3>
            <p class="wc-muted">Manage visual ecommerce products, resources, prices and catalogue placement.</p>
        </div>
    </div>

    <div class="wc-rich-list">
        @forelse($items as $item)
            @php $image = $item->mainImageResource ?? null; $imageUrl = $image?->resolved_url; $price = $item->prices->first() ?? null; @endphp
            <div class="wc-rich-card">
                <div class="wc-rich-media">
                    @if($imageUrl)<img src="{{ $imageUrl }}" alt="{{ strip_tags($item->name ?? $item->reference) }}">@else<i class="fa-solid fa-box"></i>@endif
                </div>
                <div class="wc-rich-body">
                    <div class="wc-rich-title">
                        <h4><a href="{{ route('webcatalogue.products.show', $item) }}"><span class="wc-html-inline">{!! $item->name ?? $item->reference ?? 'Product #'.$item->id !!}</span></a></h4>
                        <span class="wc-badge wc-status-{{ $item->status ?? 'draft' }}">{{ $item->status ?? 'draft' }}</span>
                    </div>
                    <div class="wc-rich-description wc-html-content wc-html-summary">{!! $item->short_description ?: ($item->description ?: 'No short description defined yet.') !!}</div>
                    <div class="wc-rich-meta">
                        <span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $item->store->name ?? 'No store' }}</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-barcode"></i>{{ $item->reference ?: 'No reference' }}</span>
                        @if($item->brand)<span class="wc-rich-metric"><i class="fa-solid fa-copyright"></i>{{ $item->brand }}</span>@endif
                        @if($item->price !== null)<span class="wc-rich-metric"><i class="fa-solid fa-euro-sign"></i>{{ number_format((float)$item->price, 2, ',', ' ') }} {{ $item->currency ?? 'EUR' }}</span>@endif
                        <span class="wc-rich-metric"><i class="fa-solid fa-paperclip"></i>{{ $item->resources_count ?? 0 }} resources</span>
                        <span class="wc-rich-metric"><i class="fa-solid fa-book-open"></i>{{ $item->catalogues_count ?? 0 }} catalogues</span>
                    </div>
                </div>
                <div class="wc-rich-actions">
                    <a class="wc-action-link" href="{{ route('webcatalogue.products.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a>
                    <a class="wc-action-link" href="{{ route('webcatalogue.products.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a>
                </div>
            </div>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-box-open"></i><div><strong>No products yet.</strong><br><span>Create a product manually or import products with CSV resources.</span></div></div>
        @endforelse
    </div>

    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
