@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<div class="wc-editor-layout">
    <div class="wc-card">
        <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-photo-film"></i> Resource</span><h3>{{ $item ? 'Edit Resource' : 'Create Resource' }}</h3><p class="wc-muted">Images, audio, videos, manuals, 3D models, AR/VR files and external links.</p></div></div>
        <form id="lsg-form" method="POST" enctype="multipart/form-data" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-link"></i></span><div><h4>Owner and type</h4><p class="wc-muted">Define where this resource belongs and how it will be used.</p></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Store</label><select name="id_store" required><option value="">Select store</option>@foreach($stores ?? [] as $store)<option value="{{ $store->id }}" @selected((string)old('id_store', $item->id_store ?? '') === (string)$store->id)>{{ $store->name }}</option>@endforeach</select></div>
                <div class="wc-field"><label>Catalogue</label><select name="id_catalogue"><option value="">None</option>@foreach($catalogues ?? [] as $catalogue)<option value="{{ $catalogue->id }}" @selected((string)old('id_catalogue', $item->id_catalogue ?? '') === (string)$catalogue->id)>{{ $catalogue->name }}</option>@endforeach</select></div>
                <div class="wc-field"><label>Product</label><select name="id_product"><option value="">None</option>@foreach($products ?? [] as $product)<option value="{{ $product->id }}" @selected((string)old('id_product', $item->id_product ?? '') === (string)$product->id)>{{ $product->reference }} — {{ $product->name }}</option>@endforeach</select></div>
                <div class="wc-field"><label>Resource Type</label><select name="resource_type" required>@foreach(['image','gallery_image','thumbnail','cover','video','audio','ambient_audio','voiceover','sound_effect','music_track','manual','datasheet','assembly_instructions','model_3d','ar_file','vr_file','skybox','floor_texture','environment_background','external_link','download','logo','favicon'] as $type)<option value="{{ $type }}" @selected(old('resource_type', $item->resource_type ?? 'image') === $type)>{{ str_replace('_',' ', ucfirst($type)) }}</option>@endforeach</select></div>
                <div class="wc-field"><label>Title</label><input name="title" value="{{ old('title', $item->title ?? '') }}"></div>
                <div class="wc-field"><label>Status</label><select name="status"><option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option><option value="draft" @selected(old('status', $item->status ?? '') === 'draft')>Draft</option><option value="inactive" @selected(old('status', $item->status ?? '') === 'inactive')>Inactive</option><option value="archived" @selected(old('status', $item->status ?? '') === 'archived')>Archived</option></select></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span><div><h4>Upload or external source</h4><p class="wc-muted">Upload directly or reference an external URL. Files are stored in the correct WebCatalogue storage path.</p></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Source Type</label><select name="source_type"><option value="upload" @selected(old('source_type', $item->source_type ?? 'upload') === 'upload')>Upload</option><option value="external_url" @selected(old('source_type', $item->source_type ?? '') === 'external_url')>External URL</option><option value="generated" @selected(old('source_type', $item->source_type ?? '') === 'generated')>Generated</option></select></div>
                <div class="wc-field wc-upload-card"><label>Upload file</label><input type="file" name="uploaded_file" accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.glb,.gltf,.obj,.fbx,.usdz,.reality,.hdr,.exr,.zip,.json,.svg,.ico"><div class="wc-upload-hint">Images, audio, video, PDFs, 3D, AR, VR, skyboxes and technical files.</div></div>
                <div class="wc-field"><label>Source URL</label><input name="source_url" value="{{ old('source_url', $item->source_url ?? '') }}"></div>
                <div class="wc-field"><label>Public URL</label><input name="public_url" value="{{ old('public_url', $item->public_url ?? '') }}"></div>
                <div class="wc-field"><label>File Path</label><input name="file_path" value="{{ old('file_path', $item->file_path ?? '') }}"></div>
                <div class="wc-field"><label>Filename</label><input name="filename" value="{{ old('filename', $item->filename ?? '') }}"></div>
                <div class="wc-field"><label>MIME Type</label><input name="mime_type" value="{{ old('mime_type', $item->mime_type ?? '') }}"></div>
                <div class="wc-field"><label>File Size</label><input type="number" name="file_size" value="{{ old('file_size', $item->file_size ?? '') }}"></div>
                <div class="wc-field"><label>Extension</label><input name="extension" value="{{ old('extension', $item->extension ?? '') }}"></div>
            </div></div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-sitemap"></i></span><div><h4>Usage and metadata</h4></div></div><div class="wc-form-grid">
                <div class="wc-field"><label>Owner Type</label><input name="resource_owner_type" value="{{ old('resource_owner_type', $item->resource_owner_type ?? '') }}" placeholder="product, catalogue, store_theme..."></div>
                <div class="wc-field"><label>Owner ID</label><input type="number" name="resource_owner_id" value="{{ old('resource_owner_id', $item->resource_owner_id ?? '') }}"></div>
                <div class="wc-field"><label>Sort Order</label><input type="number" name="sort_order" value="{{ old('sort_order', $item->sort_order ?? 0) }}"></div>
                <div class="wc-field"><label><input type="checkbox" name="is_main" value="1" @checked(old('is_main', $item->is_main ?? false))> Main resource</label></div>
                <div class="wc-field" style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="4">{{ old('description', $item->description ?? '') }}</textarea></div>
                <div class="wc-field" style="grid-column:1/-1"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div>
            </div></div>
        </form>
    </div>
    <aside class="wc-preview-panel"><div class="wc-preview-card"><div class="wc-preview-media">
        @if(!empty($item?->public_url) && str_starts_with((string)$item->mime_type, 'image/'))<img src="{{ $item->public_url }}" alt="{{ $item->title }}">
        @elseif(!empty($item?->public_url) && str_starts_with((string)$item->mime_type, 'audio/'))<audio controls src="{{ $item->public_url }}"></audio>
        @elseif(!empty($item?->public_url) && str_starts_with((string)$item->mime_type, 'video/'))<video controls src="{{ $item->public_url }}"></video>
        @else<i class="fa-solid fa-photo-film wc-preview-icon"></i>@endif
    </div><div class="wc-preview-body"><h4>{{ old('title', $item->title ?? 'Resource preview') }}</h4><p class="wc-muted">{{ old('resource_type', $item->resource_type ?? 'image') }} · {{ old('source_type', $item->source_type ?? 'upload') }}</p><span class="wc-badge">{{ old('status', $item->status ?? 'active') }}</span></div></div>
    <div class="wc-preview-card"><div class="wc-preview-body"><h4>Supported resource types</h4><div class="wc-resource-type-grid"><span class="wc-resource-pill"><i class="fa-solid fa-image"></i> Image</span><span class="wc-resource-pill"><i class="fa-solid fa-volume-high"></i> Audio</span><span class="wc-resource-pill"><i class="fa-solid fa-video"></i> Video</span><span class="wc-resource-pill"><i class="fa-solid fa-cube"></i> 3D</span><span class="wc-resource-pill"><i class="fa-solid fa-vr-cardboard"></i> AR/VR</span><span class="wc-resource-pill"><i class="fa-solid fa-file-pdf"></i> Docs</span></div></div></div>
    </aside>
</div>
</div>
@endsection
