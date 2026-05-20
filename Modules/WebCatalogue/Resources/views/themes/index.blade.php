@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@php($returnTo = request('return_to'))
<div class="wc-card">
    <div class="wc-list-toolbar"><div><span class="wc-eyebrow"><i class="fa-solid fa-palette"></i> Visual identity</span><h3>Themes @if(!empty($store)) - {{ $store->name }} @endif</h3><p class="wc-muted">Store typography, colours, logos and frontend styling presets.</p></div></div>
    <div class="wc-rich-list">
    @forelse($items as $item)
        <div class="wc-rich-card">
            <div class="wc-rich-media" style="background:linear-gradient(135deg, {{ $item->primary_color ?: '#111827' }}, {{ $item->accent_color ?: '#d4af37' }});"><i class="fa-solid fa-palette" style="color:rgba(255,255,255,.75)"></i></div>
            <div class="wc-rich-body">
                <div class="wc-rich-title"><h4><a href="{{ $returnTo ? route('webcatalogue.themes.edit', ['theme' => $item, 'return_to' => $returnTo]) : route('webcatalogue.themes.show', $item) }}">{{ $item->name ?? 'Theme #'.$item->id }}</a></h4><span class="wc-badge wc-status-{{ $item->status ?? 'active' }}">{{ $item->status ?? 'active' }}</span>@if($item->is_default)<span class="wc-badge wc-status-active">Default</span>@endif</div>
                <div class="wc-rich-description">{{ $item->font_family ?: 'No font defined' }} · {{ $item->button_style ?: 'Default buttons' }} · {{ $item->card_style ?: 'Default cards' }}</div>
                <div class="wc-rich-meta"><span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $item->store->name ?? 'No store' }}</span><span class="wc-rich-metric"><i class="fa-solid fa-fill-drip"></i>{{ $item->primary_color ?: 'No primary color' }}</span><span class="wc-rich-metric"><i class="fa-solid fa-circle-half-stroke"></i>{{ $item->accent_color ?: 'No accent color' }}</span></div>
            </div>
            <div class="wc-rich-actions"><a class="wc-action-link btn-outline-primary" href="{{ route('webcatalogue.themes.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a><a class="wc-action-link btn-outline-primary" href="{{ route('webcatalogue.themes.edit', array_filter(['theme' => $item, 'return_to' => $returnTo])) }}"><i class="fa-solid fa-pencil"></i> Edit</a></div>
        </div>
    @empty
        <div class="wc-list-empty"><i class="fa-solid fa-palette"></i><div><strong>No themes yet.</strong><br><span>Create visual presets for each store.</span></div></div>
    @endforelse
    </div>
    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)<div class="wc-pagination">{{ $items->links() }}</div>@endif
</div>
</div>
@endsection
