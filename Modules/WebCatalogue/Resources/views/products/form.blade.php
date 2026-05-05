@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif
@php
    $currentPrice = $item?->prices?->first();
    $linkedPromotionIds = $item?->promotions?->pluck('id')->map(fn($id) => (string) $id)->toArray() ?? [];
@endphp

<div class="wc-editor-layout">
    <div>
        <div class="wc-card">
            <div class="wc-section-head">
                <div>
                    <span class="wc-eyebrow"><i class="fa-solid fa-box-open"></i> Product record</span>
                    <h3>{{ $item ? 'Edit Product' : 'Create Product' }}</h3>
                    <p class="wc-muted">Commercial data, descriptions and product media in one place.</p>
                </div>
            </div>
            <form id="lsg-form" method="POST" enctype="multipart/form-data" action="{{ $action }}">
                @csrf
                @if($method !== 'POST') @method($method) @endif

                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-id-card"></i></span><div><h4>Identity</h4><p class="wc-muted">Main product identification and origin.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Store</label><select name="id_store" required><option value="">Select store</option>@foreach($stores ?? [] as $store)<option value="{{ $store->id }}" @selected((string)old('id_store', $item->id_store ?? '') === (string)$store->id)>{{ $store->name }}</option>@endforeach</select></div>
                        <div class="wc-field"><label>Reference</label><input name="reference" required value="{{ old('reference', $item->reference ?? '') }}"></div>
                        <div class="wc-field"><label>Title / Name <small class="wc-field-note">HTML allowed</small></label><input name="name" required value="{{ old('name', $item->name ?? '') }}"></div>
                        <div class="wc-field"><label>Slug</label><input name="slug" value="{{ old('slug', $item->slug ?? '') }}"></div>
                        <div class="wc-field"><label>SKU</label><input name="sku" value="{{ old('sku', $item->sku ?? '') }}"></div>
                        <div class="wc-field"><label>EAN13</label><input name="ean13" value="{{ old('ean13', $item->ean13 ?? '') }}"></div>
                        <div class="wc-field"><label>External ID</label><input name="external_id" value="{{ old('external_id', $item->external_id ?? '') }}"></div>
                        <div class="wc-field"><label>External Source</label><input name="external_source" value="{{ old('external_source', $item->external_source ?? '') }}" placeholder="csv, api, prestashop..."></div>
                    </div>
                </div>

                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-tags"></i></span><div><h4>Commercial information</h4><p class="wc-muted">Base product data used in catalogue cards and quick filters.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Brand</label><input name="brand" value="{{ old('brand', $item->brand ?? '') }}"></div>
                        <div class="wc-field"><label>Category</label><input name="category" value="{{ old('category', $item->category ?? '') }}"></div>
                        <div class="wc-field"><label>Base Price</label><input type="number" step="0.0001" name="price" value="{{ old('price', $item->price ?? '') }}"></div>
                        <div class="wc-field"><label>Currency</label><input maxlength="3" name="currency" value="{{ old('currency', $item->currency ?? 'EUR') }}"></div>
                        <div class="wc-field"><label>Stock</label><input type="number" step="0.0001" name="stock" value="{{ old('stock', $item->stock ?? '') }}"></div>
                        <div class="wc-field"><label>Status</label><select name="status"><option value="draft" @selected(old('status', $item->status ?? 'draft') === 'draft')>Draft</option><option value="active" @selected(old('status', $item->status ?? '') === 'active')>Active</option><option value="inactive" @selected(old('status', $item->status ?? '') === 'inactive')>Inactive</option><option value="archived" @selected(old('status', $item->status ?? '') === 'archived')>Archived</option></select></div>
                    </div>
                </div>

                <div class="wc-form-panel" id="commercial-pricing">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-euro-sign"></i></span><div><h4>Pricing rule</h4><p class="wc-muted">Create or update the primary price rule directly from the product. The global Pricing area remains available for audits and batch management.</p></div></div>
                    <div class="wc-inline-toggle"><label><input type="checkbox" name="price_rule[enabled]" value="1" @checked(old('price_rule.enabled', $currentPrice ? 1 : 0))> Save pricing rule with this product</label></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Price Type</label><select name="price_rule[price_type]"><option value="standard" @selected(old('price_rule.price_type', $currentPrice->price_type ?? 'standard') === 'standard')>Standard</option><option value="retail" @selected(old('price_rule.price_type', $currentPrice->price_type ?? '') === 'retail')>Retail</option><option value="b2b" @selected(old('price_rule.price_type', $currentPrice->price_type ?? '') === 'b2b')>B2B</option><option value="wholesale" @selected(old('price_rule.price_type', $currentPrice->price_type ?? '') === 'wholesale')>Wholesale</option><option value="on_request" @selected(old('price_rule.price_type', $currentPrice->price_type ?? '') === 'on_request')>On request</option><option value="hidden" @selected(old('price_rule.price_type', $currentPrice->price_type ?? '') === 'hidden')>Hidden</option></select></div>
                        <div class="wc-field"><label>Currency</label><input maxlength="3" name="price_rule[currency]" value="{{ old('price_rule.currency', $currentPrice->currency ?? $item->currency ?? 'EUR') }}"></div>
                        <div class="wc-field"><label>Regular Price</label><input type="number" step="0.0001" name="price_rule[regular_price]" value="{{ old('price_rule.regular_price', $currentPrice->regular_price ?? $item->price ?? '') }}"></div>
                        <div class="wc-field"><label>Sale Price</label><input type="number" step="0.0001" name="price_rule[sale_price]" value="{{ old('price_rule.sale_price', $currentPrice->sale_price ?? '') }}"></div>
                        <div class="wc-field"><label>Tax Rate</label><input type="number" step="0.0001" name="price_rule[tax_rate]" value="{{ old('price_rule.tax_rate', $currentPrice->tax_rate ?? '') }}"></div>
                        <div class="wc-field"><label>Status</label><select name="price_rule[status]"><option value="active" @selected(old('price_rule.status', $currentPrice->status ?? 'active') === 'active')>Active</option><option value="draft" @selected(old('price_rule.status', $currentPrice->status ?? '') === 'draft')>Draft</option><option value="inactive" @selected(old('price_rule.status', $currentPrice->status ?? '') === 'inactive')>Inactive</option></select></div>
                        <div class="wc-field"><label>Valid From</label><input type="datetime-local" name="price_rule[valid_from]" value="{{ old('price_rule.valid_from', !empty($currentPrice?->valid_from) ? $currentPrice->valid_from->format('Y-m-d\TH:i') : '') }}"></div>
                        <div class="wc-field"><label>Valid Until</label><input type="datetime-local" name="price_rule[valid_until]" value="{{ old('price_rule.valid_until', !empty($currentPrice?->valid_until) ? $currentPrice->valid_until->format('Y-m-d\TH:i') : '') }}"></div>
                        <div class="wc-field"><label><input type="checkbox" name="price_rule[tax_included]" value="1" @checked(old('price_rule.tax_included', $currentPrice->tax_included ?? true))> Tax included</label></div>
                    </div>
                </div>

                <div class="wc-form-panel" id="commercial-promotions">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-bullhorn"></i></span><div><h4>Promotions</h4><p class="wc-muted">Attach existing campaigns or create a simple campaign while editing the product.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field" style="grid-column:1/-1"><label>Attach Existing Promotions</label><select name="promotion_ids[]" multiple size="4">@foreach($promotions ?? [] as $promotion)<option value="{{ $promotion->id }}" @selected(in_array((string)$promotion->id, old('promotion_ids', $linkedPromotionIds)))>{{ $promotion->name }} — {{ $promotion->badge_label ?: $promotion->status }}</option>@endforeach</select></div>
                    </div>
                    <div class="wc-inline-toggle"><label><input type="checkbox" name="promotion_rule[enabled]" value="1" @checked(old('promotion_rule.enabled', false))> Create/update a product promotion</label></div>
                    <div class="wc-form-grid">
                        <div class="wc-field"><label>Name</label><input name="promotion_rule[name]" value="{{ old('promotion_rule.name') }}" placeholder="Spring campaign"></div>
                        <div class="wc-field"><label>Slug</label><input name="promotion_rule[slug]" value="{{ old('promotion_rule.slug') }}"></div>
                        <div class="wc-field"><label>Badge Label</label><input name="promotion_rule[badge_label]" value="{{ old('promotion_rule.badge_label') }}" placeholder="Promo, New, -20%"></div>
                        <div class="wc-field"><label>Promotion Type</label><select name="promotion_rule[promotion_type]"><option value="campaign">Campaign</option><option value="discount">Discount</option><option value="highlight">Highlight</option><option value="seasonal">Seasonal</option></select></div>
                        <div class="wc-field"><label>Discount Type</label><select name="promotion_rule[discount_type]"><option value="">None</option><option value="percentage">Percentage</option><option value="fixed">Fixed amount</option><option value="custom_price">Custom price</option></select></div>
                        <div class="wc-field"><label>Discount Value</label><input type="number" step="0.0001" name="promotion_rule[discount_value]" value="{{ old('promotion_rule.discount_value') }}"></div>
                        <div class="wc-field"><label>Starts At</label><input type="datetime-local" name="promotion_rule[starts_at]" value="{{ old('promotion_rule.starts_at') }}"></div>
                        <div class="wc-field"><label>Ends At</label><input type="datetime-local" name="promotion_rule[ends_at]" value="{{ old('promotion_rule.ends_at') }}"></div>
                        <div class="wc-field"><label>Status</label><select name="promotion_rule[status]"><option value="draft">Draft</option><option value="active">Active</option><option value="expired">Expired</option><option value="archived">Archived</option></select></div>
                        <div class="wc-field"><label>Custom Sale Price For This Product</label><input type="number" step="0.0001" name="promotion_rule[custom_sale_price]" value="{{ old('promotion_rule.custom_sale_price') }}"></div>
                        <div class="wc-field" style="grid-column:1/-1"><label>Description</label><textarea name="promotion_rule[description]" rows="3">{{ old('promotion_rule.description') }}</textarea></div>
                    </div>
                </div>

                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-align-left"></i></span><div><h4>Content</h4><p class="wc-muted">Descriptions shown in catalogue and product pages.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field" style="grid-column:1/-1"><label>Short Description <small class="wc-field-note">HTML allowed</small></label><textarea name="short_description" rows="3">{{ old('short_description', $item->short_description ?? '') }}</textarea></div>
                        <div class="wc-field" style="grid-column:1/-1"><label>Description <small class="wc-field-note">HTML allowed</small></label><textarea name="description" rows="7">{{ old('description', $item->description ?? '') }}</textarea></div>
                        <div class="wc-field" style="grid-column:1/-1"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div>
                    </div>
                </div>

                <div class="wc-form-panel">
                    <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-photo-film"></i></span><div><h4>Resources upload</h4><p class="wc-muted">Upload images, documents, audio, video, 3D, AR and VR files directly with the product.</p></div></div>
                    <div class="wc-form-grid">
                        <div class="wc-field wc-upload-card"><label>Main image</label><input type="file" name="main_image" accept="image/*"><div class="wc-upload-hint">Creates a main image resource.</div></div>
                        <div class="wc-field wc-upload-card"><label>Gallery images</label><input type="file" name="gallery_images[]" accept="image/*" multiple><div class="wc-upload-hint">Creates gallery image resources.</div></div>
                        <div class="wc-field wc-upload-card"><label>3D model</label><input type="file" name="model_3d_file" accept=".glb,.gltf,.obj,.fbx"><div class="wc-upload-hint">Recommended: GLB/GLTF.</div></div>
                        <div class="wc-field wc-upload-card"><label>AR file</label><input type="file" name="ar_file" accept=".usdz,.reality,.glb,.gltf"><div class="wc-upload-hint">iOS USDZ or WebXR GLB.</div></div>
                        <div class="wc-field wc-upload-card"><label>VR file / scene</label><input type="file" name="vr_file" accept=".glb,.gltf,.json,.zip"><div class="wc-upload-hint">Scene config or VR asset.</div></div>
                        <div class="wc-field wc-upload-card"><label>Manual / datasheet</label><input type="file" name="manual_file" accept=".pdf,.doc,.docx,.xls,.xlsx"><div class="wc-upload-hint">PDF and technical documents.</div></div>
                        <div class="wc-field wc-upload-card"><label>Audio</label><input type="file" name="audio_file" accept="audio/*"><div class="wc-upload-hint">Voiceover, ambient audio or sound effects.</div></div>
                        <div class="wc-field wc-upload-card"><label>Video</label><input type="file" name="video_file" accept="video/*"><div class="wc-upload-hint">Product demo or external media fallback.</div></div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <aside class="wc-preview-panel">
        <div class="wc-preview-card">
            <div class="wc-preview-media"><i class="fa-solid fa-cube wc-preview-icon"></i></div>
            <div class="wc-preview-body">
                <h4 class="wc-html-title">{!! old('name', $item->name ?? 'Product preview') !!}</h4>
                <p class="wc-muted">{{ old('reference', $item->reference ?? 'Reference') }} · {{ old('brand', $item->brand ?? 'Brand') }}</p>
                <span class="wc-badge">{{ old('status', $item->status ?? 'draft') }}</span>
            </div>
        </div>
        <div class="wc-preview-card"><div class="wc-preview-body"><h4>Resource channels</h4><div class="wc-preview-list">
            <div class="wc-preview-item"><i class="fa-solid fa-image"></i><span>Images and gallery</span></div>
            <div class="wc-preview-item"><i class="fa-solid fa-cube"></i><span>3D model</span></div>
            <div class="wc-preview-item"><i class="fa-solid fa-vr-cardboard"></i><span>AR / VR files</span></div>
            <div class="wc-preview-item"><i class="fa-solid fa-volume-high"></i><span>Audio experience</span></div>
        </div></div></div>
    </aside>
</div>
</div>
@endsection
