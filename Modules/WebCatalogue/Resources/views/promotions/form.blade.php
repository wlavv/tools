@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<div class="wc-editor-layout">
    <div>
        <div class="wc-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-bullhorn"></i> Promotion</span><h3>{{ $item ? 'Edit Promotion' : 'Create Promotion' }}</h3><p class="wc-muted">Commercial campaigns, badges and discount configuration.</p></div></div>
            <form id="lsg-form" method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST') @method($method) @endif
                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-store"></i></span><div><h4>Scope</h4><p class="wc-muted">Store and optional catalogue scope.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Store</label><select name="id_store" required><option value="">Select store</option>@foreach($stores ?? [] as $store)<option value="{{ $store->id }}" @selected((string)old('id_store', $item->id_store ?? '') === (string)$store->id)>{{ $store->name }}</option>@endforeach</select></div>
                        <div class="wc-field"><label>Catalogue</label><select name="id_catalogue"><option value="">All / none</option>@foreach($catalogues ?? [] as $catalogue)<option value="{{ $catalogue->id }}" @selected((string)old('id_catalogue', $item->id_catalogue ?? '') === (string)$catalogue->id)>{{ $catalogue->name }}</option>@endforeach</select></div>
                    </div>
                </div>
                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-percent"></i></span><div><h4>Campaign</h4><p class="wc-muted">Name, badge, type, value and validity.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Name</label><input name="name" required value="{{ old('name', $item->name ?? '') }}"></div>
                        <div class="wc-field"><label>Slug</label><input name="slug" value="{{ old('slug', $item->slug ?? '') }}"></div>
                        <div class="wc-field"><label>Promotion Type</label><select name="promotion_type"><option value="campaign" @selected(old('promotion_type', $item->promotion_type ?? 'campaign') === 'campaign')>Campaign</option><option value="discount" @selected(old('promotion_type', $item->promotion_type ?? '') === 'discount')>Discount</option><option value="highlight" @selected(old('promotion_type', $item->promotion_type ?? '') === 'highlight')>Highlight</option><option value="seasonal" @selected(old('promotion_type', $item->promotion_type ?? '') === 'seasonal')>Seasonal</option></select></div>
                        <div class="wc-field"><label>Badge Label</label><input name="badge_label" value="{{ old('badge_label', $item->badge_label ?? '') }}" placeholder="Promo, New, Best price..."></div>
                        <div class="wc-field"><label>Discount Type</label><select name="discount_type"><option value="" @selected(old('discount_type', $item->discount_type ?? '') === '')>None</option><option value="percentage" @selected(old('discount_type', $item->discount_type ?? '') === 'percentage')>Percentage</option><option value="fixed" @selected(old('discount_type', $item->discount_type ?? '') === 'fixed')>Fixed amount</option><option value="custom_price" @selected(old('discount_type', $item->discount_type ?? '') === 'custom_price')>Custom price</option></select></div>
                        <div class="wc-field"><label>Discount Value</label><input type="number" step="0.0001" name="discount_value" value="{{ old('discount_value', $item->discount_value ?? '') }}"></div>
                        <div class="wc-field"><label>Starts At</label><input type="datetime-local" name="starts_at" value="{{ old('starts_at', !empty($item?->starts_at) ? $item->starts_at->format('Y-m-d\\TH:i') : '') }}"></div>
                        <div class="wc-field"><label>Ends At</label><input type="datetime-local" name="ends_at" value="{{ old('ends_at', !empty($item?->ends_at) ? $item->ends_at->format('Y-m-d\\TH:i') : '') }}"></div>
                        <div class="wc-field"><label>Status</label><select name="status"><option value="draft" @selected(old('status', $item->status ?? 'draft') === 'draft')>Draft</option><option value="active" @selected(old('status', $item->status ?? '') === 'active')>Active</option><option value="expired" @selected(old('status', $item->status ?? '') === 'expired')>Expired</option><option value="archived" @selected(old('status', $item->status ?? '') === 'archived')>Archived</option></select></div>
                        <div class="wc-field" style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="5">{{ old('description', $item->description ?? '') }}</textarea></div>
                        <div class="wc-field" style="grid-column:1/-1"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <aside class="wc-preview-panel"><div class="wc-preview-card"><div class="wc-preview-media"><i class="fa-solid fa-bullhorn wc-preview-icon"></i></div><div class="wc-preview-body"><h4>Promotion preview</h4><p class="wc-muted">{{ old('badge_label', $item->badge_label ?? 'Badge') }} · {{ old('promotion_type', $item->promotion_type ?? 'campaign') }}</p><span class="wc-badge">{{ old('status', $item->status ?? 'draft') }}</span></div></div><div class="wc-commercial-note"><i class="fa-solid fa-circle-info"></i><div>Simple product-specific promotions can be created directly from the product form.</div></div></aside>
</div>
</div>
@endsection
