@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<div class="wc-editor-layout">
    <div class="wc-card">
        <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-palette"></i> Store theme</span><h3>{{ $item ? 'Edit Theme' : 'Create Theme' }}</h3><p class="wc-muted">Branding, colors, typography and visual behaviour for a store.</p></div></div>
        <form id="lsg-form" method="POST" enctype="multipart/form-data" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-id-badge"></i></span><div><h4>Theme identity</h4></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Store</label><select name="id_store" required><option value="">Select store</option>@foreach($stores ?? [] as $store)<option value="{{ $store->id }}" @selected((string)old('id_store', $item->id_store ?? '') === (string)$store->id)>{{ $store->name }}</option>@endforeach</select></div>
                <div class="wc-field"><label>Name</label><input name="name" required value="{{ old('name', $item->name ?? '') }}"></div>
                <div class="wc-field"><label>Slug</label><input name="slug" value="{{ old('slug', $item->slug ?? '') }}"></div>
                <div class="wc-field"><label>Status</label><select name="status"><option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option><option value="draft" @selected(old('status', $item->status ?? '') === 'draft')>Draft</option><option value="inactive" @selected(old('status', $item->status ?? '') === 'inactive')>Inactive</option></select></div>
                <div class="wc-field"><label><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $item->is_default ?? false))> Default theme</label></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-image"></i></span><div><h4>Brand resources</h4><p class="wc-muted">Upload logos directly; resource IDs are filled automatically.</p></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Logo Resource ID</label><input type="number" name="logo_resource_id" value="{{ old('logo_resource_id', $item->logo_resource_id ?? '') }}"></div>
                <div class="wc-field wc-upload-card"><label>Upload logo</label><input type="file" name="logo_upload" accept="image/*,.svg"><div class="wc-upload-hint">Logo for public catalogue and viewer UI.</div></div>
                <div class="wc-field"><label>Favicon Resource ID</label><input type="number" name="favicon_resource_id" value="{{ old('favicon_resource_id', $item->favicon_resource_id ?? '') }}"></div>
                <div class="wc-field wc-upload-card"><label>Upload favicon</label><input type="file" name="favicon_upload" accept="image/*,.ico,.svg"><div class="wc-upload-hint">Browser icon / public page favicon.</div></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-font"></i></span><div><h4>Typography and colors</h4></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Font Family</label><input name="font_family" value="{{ old('font_family', $item->font_family ?? '') }}"></div>
                <div class="wc-field"><label>Heading Font Family</label><input name="heading_font_family" value="{{ old('heading_font_family', $item->heading_font_family ?? '') }}"></div>
                @foreach(['primary_color'=>'Primary Color','secondary_color'=>'Secondary Color','accent_color'=>'Accent Color','background_color'=>'Background Color','text_color'=>'Text Color'] as $field => $label)
                    <div class="wc-field"><label>{{ $label }}</label><div class="wc-color-row"><input type="color" value="{{ old($field, $item->{$field} ?? '#111827') ?: '#111827' }}" onchange="this.nextElementSibling.value=this.value"><input name="{{ $field }}" value="{{ old($field, $item->{$field} ?? '') }}" placeholder="#111827"></div></div>
                @endforeach
                <div class="wc-field"><label>Button Style</label><input name="button_style" value="{{ old('button_style', $item->button_style ?? '') }}" placeholder="rounded, premium, minimal..."></div>
                <div class="wc-field"><label>Card Style</label><input name="card_style" value="{{ old('card_style', $item->card_style ?? '') }}"></div>
                <div class="wc-field"><label>Border Radius</label><input name="border_radius" value="{{ old('border_radius', $item->border_radius ?? '5px') }}"></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-code"></i></span><div><h4>Advanced</h4></div></div><div class="wc-field"><label>Custom CSS</label><textarea name="custom_css" rows="8">{{ old('custom_css', $item->custom_css ?? '') }}</textarea></div><div class="wc-field"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div></div>
        </form>
    </div>
    <aside class="wc-preview-panel"><div class="wc-preview-card"><div class="wc-preview-body"><h4>Theme preview</h4><div class="wc-live-preview" style="background:{{ old('background_color', $item->background_color ?? '#ffffff') ?: '#ffffff' }};color:{{ old('text_color', $item->text_color ?? '#111827') ?: '#111827' }}"><strong style="font-family:{{ old('heading_font_family', $item->heading_font_family ?? 'inherit') }};color:{{ old('primary_color', $item->primary_color ?? '#111827') ?: '#111827' }}">{{ old('name', $item->name ?? 'Premium catalogue') }}</strong><p class="wc-muted">Visual identity sample</p><span class="wc-demo-button" style="background:{{ old('accent_color', $item->accent_color ?? '#111827') ?: '#111827' }}">Call to action</span></div></div></div></aside>
</div>
</div>
@endsection
