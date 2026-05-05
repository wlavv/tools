@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-hero-card">
    <div><span class="wc-eyebrow"><i class="fa-solid fa-bullhorn"></i> Commercial layer</span><h2>Promotions</h2><p>Global campaign management for product badges, discounts, seasonal highlights and catalogue-wide promotions.</p></div>
</div>

<div class="wc-commercial-note"><i class="fa-solid fa-circle-info"></i><div><strong>Recommended workflow:</strong> attach/create simple promotions from the product form. Use this area to manage broader campaigns and catalogue promotions.</div></div>

<div class="wc-card">
    <div class="wc-section-head"><div><h3>Promotion campaigns</h3><p class="wc-muted">Campaigns, discounts and product highlights.</p></div></div>
    @forelse($items as $item)
        <div class="wc-admin-list-card">
            <div class="wc-admin-list-icon"><i class="fa-solid fa-percent"></i></div>
            <div>
                <h4>{{ $item->name ?? 'Promotion' }}</h4>
                <p class="wc-muted">{{ ucfirst($item->promotion_type ?? 'campaign') }} · {{ $item->badge_label ?: 'No badge' }}</p>
                <div class="wc-admin-list-meta">
                    @if($item->discount_type)<span class="wc-badge">{{ $item->discount_type }} {{ $item->discount_value }}</span>@endif
                    <span class="wc-badge">{{ $item->status ?? 'draft' }}</span>
                    @if($item->starts_at)<span class="wc-badge">From {{ $item->starts_at->format('Y-m-d') }}</span>@endif
                    @if($item->ends_at)<span class="wc-badge">Until {{ $item->ends_at->format('Y-m-d') }}</span>@endif
                </div>
            </div>
            <div class="wc-actions-row"><a class="wc-action-link" href="{{ route('webcatalogue.promotions.show', $item) }}"><i class="fa-solid fa-eye"></i> Open</a><a class="wc-action-link" href="{{ route('webcatalogue.promotions.edit', $item) }}"><i class="fa-solid fa-pencil"></i> Edit</a></div>
        </div>
    @empty
        <div class="wc-empty-state"><i class="fa-solid fa-bullhorn"></i><span>No promotions yet. Create them from a product or from this area.</span></div>
    @endforelse

    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
