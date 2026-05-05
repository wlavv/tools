@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
<div class="wc-card">
    <div class="wc-list-toolbar"><div><span class="wc-eyebrow"><i class="fa-solid fa-bullseye"></i> Visual Recognition</span><h3>Unmatched product leads</h3><p class="wc-muted">Products users tried to identify but are not yet in the catalogue.</p></div></div>
    <div class="wc-rich-list">
        @forelse($items as $item)
            <div class="wc-rich-card">
                <div class="wc-rich-media"><i class="fa-solid fa-lightbulb"></i></div>
                <div class="wc-rich-body"><div class="wc-rich-title"><h4><a href="{{ route('webcatalogue.recognition.leads.show', $item) }}">{{ $item->brand ?: 'Unknown brand' }}</a></h4><span class="wc-badge">{{ $item->status }}</span></div><div class="wc-rich-description">{{ $item->model ?: $item->reference ?: 'No model/reference provided' }}</div><div class="wc-rich-meta"><span class="wc-rich-metric"><i class="fa-solid fa-store"></i>{{ $item->store->name ?? '—' }}</span><span class="wc-rich-metric"><i class="fa-solid fa-star"></i>Score {{ $item->lead_score }}</span><span class="wc-rich-metric"><i class="fa-solid fa-clock"></i>{{ $item->created_at?->format('Y-m-d H:i') }}</span></div></div>
                <div class="wc-rich-actions"><a class="wc-action-link" href="{{ route('webcatalogue.recognition.leads.show', $item) }}"><i class="fa-solid fa-eye"></i> View</a></div>
            </div>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-lightbulb"></i><div><strong>No leads yet.</strong></div></div>
        @endforelse
    </div>
    <div class="wc-pagination">{{ $items->links() }}</div>
</div>
</div>
@endsection
