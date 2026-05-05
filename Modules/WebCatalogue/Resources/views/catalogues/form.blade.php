@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<div class="wc-editor-layout">
    <div class="wc-card">
        <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-book-open"></i> Catalogue</span><h3>{{ $item ? 'Edit Catalogue' : 'Create Catalogue' }}</h3><p class="wc-muted">Catalogue presentation, visibility, pricing and promotion behaviour.</p></div></div>
        <form id="lsg-form" method="POST" enctype="multipart/form-data" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-layer-group"></i></span><div><h4>Base information</h4><p class="wc-muted">Store association and catalogue identity.</p></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Store</label><select name="id_store" required><option value="">Select store</option>@foreach($stores ?? [] as $store)<option value="{{ $store->id }}" @selected((string)old('id_store', $item->id_store ?? '') === (string)$store->id)>{{ $store->name }}</option>@endforeach</select></div>
                <div class="wc-field"><label>Name</label><input name="name" required value="{{ old('name', $item->name ?? '') }}"></div>
                <div class="wc-field"><label>Slug</label><input name="slug" value="{{ old('slug', $item->slug ?? '') }}"></div>
                <div class="wc-field"><label>Cover Resource ID</label><input type="number" name="cover_resource_id" value="{{ old('cover_resource_id', $item->cover_resource_id ?? '') }}"></div>
                <div class="wc-field wc-upload-card"><label>Upload cover</label><input type="file" name="cover_upload" accept="image/*"><div class="wc-upload-hint">Creates a cover resource and links it to this catalogue.</div></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-eye"></i></span><div><h4>Publication behaviour</h4><p class="wc-muted">Define whether this is a showcase, price list or campaign catalogue.</p></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Catalogue Type</label><select name="catalogue_type"><option value="showcase" @selected(old('catalogue_type', $item->catalogue_type ?? 'showcase') === 'showcase')>Showcase</option><option value="price_list" @selected(old('catalogue_type', $item->catalogue_type ?? '') === 'price_list')>Price list</option><option value="campaign" @selected(old('catalogue_type', $item->catalogue_type ?? '') === 'campaign')>Campaign</option><option value="mixed" @selected(old('catalogue_type', $item->catalogue_type ?? '') === 'mixed')>Mixed</option></select></div>
                <div class="wc-field"><label>Price Mode</label><select name="price_mode"><option value="hidden" @selected(old('price_mode', $item->price_mode ?? 'hidden') === 'hidden')>Hidden</option><option value="visible" @selected(old('price_mode', $item->price_mode ?? '') === 'visible')>Visible</option><option value="on_request" @selected(old('price_mode', $item->price_mode ?? '') === 'on_request')>On request</option><option value="login_required" @selected(old('price_mode', $item->price_mode ?? '') === 'login_required')>Login required</option></select></div>
                <div class="wc-field"><label>Visibility</label><select name="visibility"><option value="private" @selected(old('visibility', $item->visibility ?? 'private') === 'private')>Private</option><option value="public" @selected(old('visibility', $item->visibility ?? '') === 'public')>Public</option><option value="token" @selected(old('visibility', $item->visibility ?? '') === 'token')>Token</option></select></div>
                <div class="wc-field"><label>Status</label><select name="status"><option value="draft" @selected(old('status', $item->status ?? 'draft') === 'draft')>Draft</option><option value="active" @selected(old('status', $item->status ?? '') === 'active')>Active</option><option value="published" @selected(old('status', $item->status ?? '') === 'published')>Published</option><option value="archived" @selected(old('status', $item->status ?? '') === 'archived')>Archived</option></select></div>
                <div class="wc-field"><label>Published at</label><input type="datetime-local" name="published_at" value="{{ old('published_at', !empty($item?->published_at) ? $item->published_at->format('Y-m-d\TH:i') : '') }}"></div>
                <div class="wc-field"><label><input type="checkbox" name="show_prices" value="1" @checked(old('show_prices', $item->show_prices ?? false))> Show prices</label></div>
                <div class="wc-field"><label><input type="checkbox" name="show_promotions" value="1" @checked(old('show_promotions', $item->show_promotions ?? false))> Show promotions</label></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-align-left"></i></span><div><h4>Content</h4></div></div><div class="wc-field"><label>Description</label><textarea name="description" rows="4">{{ old('description', $item->description ?? '') }}</textarea></div><div class="wc-field"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div></div>
        </form>
    </div>
    <aside class="wc-preview-panel"><div class="wc-preview-card"><div class="wc-preview-media"><i class="fa-solid fa-book-open wc-preview-icon"></i></div><div class="wc-preview-body"><h4>{{ old('name', $item->name ?? 'Catalogue preview') }}</h4><p class="wc-muted">{{ old('catalogue_type', $item->catalogue_type ?? 'showcase') }} · {{ old('visibility', $item->visibility ?? 'private') }}</p><span class="wc-badge">{{ old('status', $item->status ?? 'draft') }}</span></div></div></aside>
</div>
</div>
@endsection
