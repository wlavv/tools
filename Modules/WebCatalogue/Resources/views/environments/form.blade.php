@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<div class="wc-editor-layout">
    <div class="wc-card">
        <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-vr-cardboard"></i> Environment</span><h3>{{ $item ? 'Edit Environment' : 'Create Environment' }}</h3><p class="wc-muted">3D/AR/VR environment configuration for a store.</p></div></div>
        <form id="lsg-form" method="POST" enctype="multipart/form-data" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-cube"></i></span><div><h4>Scene identity</h4></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Store</label><select name="id_store" required><option value="">Select store</option>@foreach($stores ?? [] as $store)<option value="{{ $store->id }}" @selected((string)old('id_store', $item->id_store ?? '') === (string)$store->id)>{{ $store->name }}</option>@endforeach</select></div>
                <div class="wc-field"><label>Name</label><input name="name" required value="{{ old('name', $item->name ?? '') }}"></div>
                <div class="wc-field"><label>Slug</label><input name="slug" value="{{ old('slug', $item->slug ?? '') }}"></div>
                <div class="wc-field"><label>Environment Type</label><select name="environment_type"><option value="showroom" @selected(old('environment_type', $item->environment_type ?? 'showroom') === 'showroom')>Showroom</option><option value="studio" @selected(old('environment_type', $item->environment_type ?? '') === 'studio')>Studio</option><option value="outdoor" @selected(old('environment_type', $item->environment_type ?? '') === 'outdoor')>Outdoor</option><option value="ar" @selected(old('environment_type', $item->environment_type ?? '') === 'ar')>AR</option><option value="vr" @selected(old('environment_type', $item->environment_type ?? '') === 'vr')>VR</option></select></div>
                <div class="wc-field"><label>Status</label><select name="status"><option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option><option value="draft" @selected(old('status', $item->status ?? '') === 'draft')>Draft</option><option value="inactive" @selected(old('status', $item->status ?? '') === 'inactive')>Inactive</option></select></div>
                <div class="wc-field"><label><input type="checkbox" name="is_default" value="1" @checked(old('is_default', $item->is_default ?? false))> Default environment</label></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-mountain-sun"></i></span><div><h4>Background and assets</h4><p class="wc-muted">Upload skyboxes, floor textures, ambient audio and VR files.</p></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Background Type</label><select name="background_type"><option value="color" @selected(old('background_type', $item->background_type ?? 'color') === 'color')>Color</option><option value="image" @selected(old('background_type', $item->background_type ?? '') === 'image')>Image</option><option value="skybox" @selected(old('background_type', $item->background_type ?? '') === 'skybox')>Skybox</option><option value="hdr" @selected(old('background_type', $item->background_type ?? '') === 'hdr')>HDR</option></select></div>
                <div class="wc-field"><label>Background Color</label><div class="wc-color-row"><input type="color" value="{{ old('background_color', $item->background_color ?? '#f8fafc') ?: '#f8fafc' }}" onchange="this.nextElementSibling.value=this.value"><input name="background_color" value="{{ old('background_color', $item->background_color ?? '') }}"></div></div>
                <div class="wc-field"><label>Background Resource ID</label><input type="number" name="background_resource_id" value="{{ old('background_resource_id', $item->background_resource_id ?? '') }}"></div>
                <div class="wc-field wc-upload-card"><label>Upload background</label><input type="file" name="background_upload" accept="image/*,.hdr,.exr"><div class="wc-upload-hint">Background image/HDR.</div></div>
                <div class="wc-field"><label>Skybox Resource ID</label><input type="number" name="skybox_resource_id" value="{{ old('skybox_resource_id', $item->skybox_resource_id ?? '') }}"></div>
                <div class="wc-field wc-upload-card"><label>Upload skybox/HDR</label><input type="file" name="skybox_upload" accept="image/*,.hdr,.exr"><div class="wc-upload-hint">360º skybox or HDR lighting.</div></div>
                <div class="wc-field"><label>Floor Resource ID</label><input type="number" name="floor_resource_id" value="{{ old('floor_resource_id', $item->floor_resource_id ?? '') }}"></div>
                <div class="wc-field wc-upload-card"><label>Upload floor texture</label><input type="file" name="floor_upload" accept="image/*"><div class="wc-upload-hint">Floor/grid/material texture.</div></div>
                <div class="wc-field wc-upload-card"><label>Ambient audio</label><input type="file" name="ambient_audio_upload" accept="audio/*"><div class="wc-upload-hint">Immersive background sound.</div></div>
                <div class="wc-field wc-upload-card"><label>VR scene file</label><input type="file" name="vr_scene_upload" accept=".json,.glb,.gltf,.zip"><div class="wc-upload-hint">Optional VR scene config/asset.</div></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-camera"></i></span><div><h4>Lighting and camera</h4></div></div><div class="wc-form-grid"><div class="wc-field"><label>Lighting Preset</label><input name="lighting_preset" value="{{ old('lighting_preset', $item->lighting_preset ?? '') }}" placeholder="soft_studio, daylight..."></div><div class="wc-field"><label>Camera Preset</label><input name="camera_preset" value="{{ old('camera_preset', $item->camera_preset ?? '') }}"></div></div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-code"></i></span><div><h4>Scene configs</h4></div></div><div class="wc-field"><label>VR Scene Config JSON</label><textarea name="vr_scene_config" rows="6">{{ old('vr_scene_config', isset($item->vr_scene_config) ? json_encode($item->vr_scene_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div><div class="wc-field"><label>AR Scene Config JSON</label><textarea name="ar_scene_config" rows="6">{{ old('ar_scene_config', isset($item->ar_scene_config) ? json_encode($item->ar_scene_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div><div class="wc-field"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div></div>
        </form>
    </div>
    <aside class="wc-preview-panel"><div class="wc-preview-card"><div class="wc-preview-media" style="background:{{ old('background_color', $item->background_color ?? '#f8fafc') ?: '#f8fafc' }}"><i class="fa-solid fa-vr-cardboard wc-preview-icon"></i></div><div class="wc-preview-body"><h4>{{ old('name', $item->name ?? 'Environment preview') }}</h4><p class="wc-muted">{{ old('environment_type', $item->environment_type ?? 'showroom') }} · {{ old('background_type', $item->background_type ?? 'color') }}</p><span class="wc-badge">{{ old('status', $item->status ?? 'active') }}</span></div></div></aside>
</div>
</div>
@endsection
