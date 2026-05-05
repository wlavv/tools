@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-hero-card">
    <div><span class="wc-eyebrow"><i class="fa-solid fa-tags"></i> Commercial layer</span><h2>Pricing</h2><p>Global audit and management of all product price rules. Day-to-day pricing can also be edited directly inside each product.</p></div>
</div>

<div class="wc-commercial-note"><i class="fa-solid fa-circle-info"></i><div><strong>Recommended workflow:</strong> manage the primary price rule inside the product form. Use this area for global reviews, exceptions, B2B/wholesale lists and time-limited price rules.</div></div>

<div class="wc-card">
    <div class="wc-section-head"><div><h3>Price rules</h3><p class="wc-muted">Retail, B2B, wholesale, hidden and on-request prices.</p></div></div>
    @forelse($items as $item)
        <div class="wc-admin-list-card">
            <div class="wc-admin-list-icon"><i class="fa-solid fa-euro-sign"></i></div>
            <div>
                <h4>{{ $item->product->reference ?? 'Product #'.$item->id_product }} — {{ $item->product->name ?? 'Product price' }}</h4>
                <p class="wc-muted">{{ ucfirst(str_replace('_',' ', $item->price_type ?? 'standard')) }} · {{ $item->currency ?? 'EUR' }}</p>
                <div class="wc-admin-list-meta">
                    <span class="wc-badge">{{ $item->regular_price !== null ? number_format((float)$item->regular_price, 2, ',', ' ') : '—' }} {{ $item->currency }}</span>
                    @if($item->sale_price)<span class="wc-badge wc-badge-invalid">Sale {{ number_format((float)$item->sale_price, 2, ',', ' ') }} {{ $item->currency }}</span>@endif
                    <span class="wc-badge">{{ $item->status ?? 'active' }}</span>
                </div>
            </div>
            <div class="wc-actions-row"><a class="wc-action-link" href="{{ route('webcatalogue.pricing.show', $item) }}"><i class="fa-solid fa-eye"></i> Open</a>@if($item->product)<a class="wc-action-link" href="{{ route('webcatalogue.products.edit', $item->product) }}#commercial-pricing"><i class="fa-solid fa-box-open"></i> Product</a>@endif</div>
        </div>
    @empty
        <div class="wc-empty-state"><i class="fa-solid fa-tag"></i><span>No price rules yet. Create them from product forms or import CSV pricing.</span></div>
    @endforelse

    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
