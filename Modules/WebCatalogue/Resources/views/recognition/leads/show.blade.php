@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
<div class="wc-editor-layout">
    <div>
        <div class="wc-detail-hero wc-detail-hero-product"><div><span class="wc-eyebrow"><i class="fa-solid fa-bullseye"></i> Unmatched Product Lead</span><h2>{{ $item->brand ?: 'Unknown brand' }}</h2><p>{{ $item->model ?: $item->reference ?: 'No model/reference provided' }}</p><div class="wc-detail-tags"><span class="wc-badge">{{ $item->status }}</span><span class="wc-badge">Score {{ $item->lead_score }}</span></div></div><div class="wc-detail-icon"><i class="fa-solid fa-lightbulb"></i></div></div>
        <div class="wc-card wc-spaced-card"><div class="wc-section-head"><div><h3>Lead details</h3></div></div><div class="wc-keyval-grid"><div class="wc-keyval"><span>Brand</span><strong>{{ $item->brand ?: '—' }}</strong></div><div class="wc-keyval"><span>Model</span><strong>{{ $item->model ?: '—' }}</strong></div><div class="wc-keyval"><span>Reference</span><strong>{{ $item->reference ?: '—' }}</strong></div><div class="wc-keyval"><span>Email</span><strong>{{ $item->customer_email ?: '—' }}</strong></div></div><p style="margin-top:16px">{{ $item->description ?: 'No description.' }}</p></div>
        @if($item->session && $item->session->captures->count())<div class="wc-card wc-spaced-card"><div class="wc-section-head"><div><h3>Submitted images</h3></div></div><div class="wc-grid">@foreach($item->session->captures as $capture)<div class="wc-preview-card"><div class="wc-preview-media">@if($capture->resolved_url)<img src="{{ $capture->resolved_url }}">@else<i class="fa-solid fa-image"></i>@endif</div><div class="wc-preview-body"><h4>{{ str_replace('_',' ', $capture->capture_type) }}</h4></div></div>@endforeach</div></div>@endif
    </div>
    <aside class="wc-preview-panel"><div class="wc-preview-card"><div class="wc-preview-body"><h4>Status</h4><form method="POST" action="{{ route('webcatalogue.recognition.leads.status', $item) }}">@csrf<select name="status" class="form-control"><option value="new" @selected($item->status==='new')>New</option><option value="reviewing" @selected($item->status==='reviewing')>Reviewing</option><option value="qualified" @selected($item->status==='qualified')>Qualified</option><option value="contacted_brand" @selected($item->status==='contacted_brand')>Contacted Brand</option><option value="converted" @selected($item->status==='converted')>Converted</option><option value="ignored" @selected($item->status==='ignored')>Ignored</option></select><textarea name="notes" class="form-control" rows="4" style="margin-top:10px" placeholder="Notes">{{ $item->notes }}</textarea><button class="wc-primary-btn" style="margin-top:10px" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save status</button></form></div></div></aside>
</div>
</div>
@endsection
