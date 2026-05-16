@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<div class="wc-editor-layout">
    <div>
        <div class="wc-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-tags"></i> Price rule</span><h3>{{ $item ? 'Edit Price' : 'Create Price' }}</h3><p class="wc-muted">Advanced product pricing. Prefer product form for everyday edits.</p></div></div>
            <form id="lsg-form" method="POST" action="{{ $action }}">
                @csrf
                @if($method !== 'POST') @method($method) @endif
                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-box-open"></i></span><div><h4>Product</h4><p class="wc-muted">Store and product to which this rule applies.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Store</label><select name="id_store" required><option value="">Select store</option>@foreach($stores ?? [] as $store)<option value="{{ $store->id }}" @selected((string)old('id_store', $item->id_store ?? request('id_store', '')) === (string)$store->id)>{{ $store->name }}</option>@endforeach</select></div>
                        <div class="wc-field"><label>Product</label><select name="id_product" required><option value="">Select product</option>@foreach($products ?? [] as $product)<option value="{{ $product->id }}" @selected((string)old('id_product', $item->id_product ?? '') === (string)$product->id)>{{ $product->reference }} — {{ $product->name }}</option>@endforeach</select></div>
                    </div>
                </div>
                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-euro-sign"></i></span><div><h4>Pricing</h4><p class="wc-muted">Visibility mode, regular price, sale price, taxes and validity.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Price Type</label><select name="price_type"><option value="standard" @selected(old('price_type', $item->price_type ?? 'standard') === 'standard')>Standard</option><option value="retail" @selected(old('price_type', $item->price_type ?? '') === 'retail')>Retail</option><option value="b2b" @selected(old('price_type', $item->price_type ?? '') === 'b2b')>B2B</option><option value="wholesale" @selected(old('price_type', $item->price_type ?? '') === 'wholesale')>Wholesale</option><option value="on_request" @selected(old('price_type', $item->price_type ?? '') === 'on_request')>On request</option><option value="hidden" @selected(old('price_type', $item->price_type ?? '') === 'hidden')>Hidden</option></select></div>
                        <div class="wc-field"><label>Currency</label><input maxlength="3" name="currency" value="{{ old('currency', $item->currency ?? 'EUR') }}"></div>
                        <div class="wc-field"><label>Regular Price</label><input type="number" step="0.0001" name="regular_price" value="{{ old('regular_price', $item->regular_price ?? '') }}"></div>
                        <div class="wc-field"><label>Sale Price</label><input type="number" step="0.0001" name="sale_price" value="{{ old('sale_price', $item->sale_price ?? '') }}"></div>
                        <div class="wc-field"><label>Tax Rate</label><input type="number" step="0.0001" name="tax_rate" value="{{ old('tax_rate', $item->tax_rate ?? '') }}"></div>
                        <div class="wc-field"><label>Status</label><select name="status"><option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option><option value="draft" @selected(old('status', $item->status ?? '') === 'draft')>Draft</option><option value="inactive" @selected(old('status', $item->status ?? '') === 'inactive')>Inactive</option></select></div>
                        <div class="wc-field"><label>Valid From</label><input type="datetime-local" name="valid_from" value="{{ old('valid_from', !empty($item?->valid_from) ? $item->valid_from->format('Y-m-d\\TH:i') : '') }}"></div>
                        <div class="wc-field"><label>Valid Until</label><input type="datetime-local" name="valid_until" value="{{ old('valid_until', !empty($item?->valid_until) ? $item->valid_until->format('Y-m-d\\TH:i') : '') }}"></div>
                        <div class="wc-field"><label><input type="checkbox" name="tax_included" value="1" @checked(old('tax_included', $item->tax_included ?? true))> Tax included</label></div>
                        <div class="wc-field" style="grid-column:1/-1"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <aside class="wc-preview-panel"><div class="wc-preview-card"><div class="wc-preview-media"><i class="fa-solid fa-tags wc-preview-icon"></i></div><div class="wc-preview-body"><h4>Price preview</h4><p class="wc-muted">{{ old('regular_price', $item->regular_price ?? '0.00') }} {{ old('currency', $item->currency ?? 'EUR') }}</p><span class="wc-badge">{{ old('price_type', $item->price_type ?? 'standard') }}</span></div></div><div class="wc-commercial-note"><i class="fa-solid fa-circle-info"></i><div>This page is for advanced/global pricing. The product form also includes direct pricing management.</div></div></aside>
</div>
</div>
@endsection
