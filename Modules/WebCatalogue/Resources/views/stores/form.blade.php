@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="wc-alert wc-alert-warning">{{ $errors->first() }}</div>@endif

<div class="wc-editor-layout">
    <div class="wc-card">
        <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-store"></i> Store</span><h3>{{ $item ? 'Edit Store' : 'Create Store' }}</h3><p class="wc-muted">Commercial owner/client for catalogues, products, themes and environments.</p></div></div>
        <form id="lsg-form" method="POST" enctype="multipart/form-data" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="wc-form-panel">
                <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-building"></i></span><div><h4>Store identity</h4><p class="wc-muted">Core identification used by imports and public URLs.</p></div></div>
                <div class="wc-form-grid">
                    <div class="wc-field"><label>Name</label><input name="name" required value="{{ old('name', $item->name ?? '') }}"></div>
                    <div class="wc-field"><label>Slug</label><input name="slug" value="{{ old('slug', $item->slug ?? '') }}" placeholder="auto-generated if empty"></div>
                    <div class="wc-field"><label>Code</label><input name="code" required value="{{ old('code', $item->code ?? '') }}" placeholder="LS, TCG, ASD..."></div>
                    <div class="wc-field"><label>Domain</label><input name="domain" value="{{ old('domain', $item->domain ?? '') }}" placeholder="example.com"></div>
                    <div class="wc-field"><label>Status</label><select name="status"><option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option><option value="draft" @selected(old('status', $item->status ?? '') === 'draft')>Draft</option><option value="inactive" @selected(old('status', $item->status ?? '') === 'inactive')>Inactive</option><option value="archived" @selected(old('status', $item->status ?? '') === 'archived')>Archived</option></select></div>
                    <div class="wc-field"><label>Logo path / URL</label><input name="logo_path" value="{{ old('logo_path', $item->logo_path ?? '') }}"></div>
                </div>
            </div>
            <div class="wc-form-panel">
                <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-image"></i></span><div><h4>Branding resource</h4><p class="wc-muted">Upload a logo and keep it registered as a WebCatalogue resource.</p></div></div>
                <div class="wc-form-grid"><div class="wc-field wc-upload-card"><label>Upload logo</label><input type="file" name="logo_upload" accept="image/*,.svg"><div class="wc-upload-hint">PNG, JPG, WebP or SVG. The store logo path is updated automatically.</div></div></div>
            </div>
            <div class="wc-form-panel">
                <div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-palette"></i></span><div><h4>Public front configuration</h4><p class="wc-muted">Controls the public catalogue page accessed by client links. These settings are saved in store metadata so the front can change without code changes.</p></div></div>
                @php
                    $front = is_array($item->metadata ?? null) ? (($item->metadata['front'] ?? []) ?: []) : [];
                    $frontLayouts = config('webcatalogue_front_layouts', []);
                    if (empty($frontLayouts)) {
                        $frontLayouts = [
                            'classic_catalogue' => ['label' => 'Classic Catalogue', 'description' => 'Clean catalogue grid.'],
                            'premium_showroom' => ['label' => 'Premium Showroom', 'description' => 'Premium commercial feel.'],
                            'minimal_white' => ['label' => 'Minimal White', 'description' => 'Clean product-first layout.'],
                            'dark_immersive' => ['label' => 'Dark Immersive', 'description' => 'Dark 3D/AR/VR layout.'],
                            'luxury_gold' => ['label' => 'Luxury Gold', 'description' => 'High-end gold accents.'],
                            'technical_b2b' => ['label' => 'Technical B2B', 'description' => 'Structured technical layout.'],
                            'visual_gallery' => ['label' => 'Visual Gallery', 'description' => 'Image-heavy browsing.'],
                            'compact_sales' => ['label' => 'Compact Sales', 'description' => 'Dense sales grid.'],
                            'campaign_promo' => ['label' => 'Campaign / Promo', 'description' => 'Promotion-focused landing.'],
                            'immersive_3d' => ['label' => 'Immersive 3D', 'description' => 'Viewer-first layout.'],
                        ];
                    }
                @endphp
                <div class="wc-help-note"><i class="fa-solid fa-circle-info"></i><div>Configure the public store experience here. The preview on the right updates instantly so you can validate the look before saving.</div></div>
                <div class="wc-theme-presets" aria-label="Theme presets">
                    <button type="button" class="wc-theme-preset-btn" data-wc-preset="minimal">Minimal Clean</button>
                    <button type="button" class="wc-theme-preset-btn" data-wc-preset="premium">Premium Dark</button>
                    <button type="button" class="wc-theme-preset-btn" data-wc-preset="gold">Luxury Gold</button>
                    <button type="button" class="wc-theme-preset-btn" data-wc-preset="technical">Technical B2B</button>
                </div>
                <div class="wc-form-grid">
                    <div class="wc-field" style="grid-column:1/-1">
                        <label>Frontoffice layout</label>
                        <select name="front_layout">
                            @foreach($frontLayouts as $layoutKey => $layout)
                                <option value="{{ $layoutKey }}" @selected(old('front_layout', $front['layout'] ?? 'classic_catalogue') === $layoutKey)>{{ $layout['label'] ?? $layoutKey }} — {{ $layout['description'] ?? '' }}</option>
                            @endforeach
                        </select>
                        <div class="wc-upload-hint">Choose the visual preset used by /catalogue/{{ $item->slug ?? '{store_slug}' }}.</div>
                    </div>

                    <div class="wc-field"><label>Primary color</label><input type="color" name="front_primary_color" value="{{ old('front_primary_color', $front['primary_color'] ?? '#b68b2d') }}"></div>
                    <div class="wc-field"><label>Secondary color</label><input type="color" name="front_secondary_color" value="{{ old('front_secondary_color', $front['secondary_color'] ?? '#f1d28a') }}"></div>
                    <div class="wc-field"><label>Header color</label><input type="color" name="front_header_color" value="{{ old('front_header_color', $front['header_color'] ?? '#ffffff') }}"></div>
                    <div class="wc-field"><label>Header text color</label><input type="color" name="front_header_text_color" value="{{ old('front_header_text_color', $front['header_text_color'] ?? '#151923') }}"></div>
                    <div class="wc-field"><label>Background color</label><input type="color" name="front_background_color" value="{{ old('front_background_color', $front['background_color'] ?? '#f6f7fb') }}"></div>
                    <div class="wc-field"><label>Surface/card color</label><input type="color" name="front_surface_color" value="{{ old('front_surface_color', $front['surface_color'] ?? '#ffffff') }}"></div>
                    <div class="wc-field"><label>Text color</label><input type="color" name="front_text_color" value="{{ old('front_text_color', $front['text_color'] ?? '#151923') }}"></div>
                    <div class="wc-field"><label>Muted text color</label><input type="color" name="front_muted_text_color" value="{{ old('front_muted_text_color', $front['muted_text_color'] ?? '#687386') }}"></div>

                    <div class="wc-field"><label>Body font</label><input name="front_font_family" value="{{ old('front_font_family', $front['font_family'] ?? 'Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif') }}"></div>
                    <div class="wc-field"><label>Heading font</label><input name="front_heading_font_family" value="{{ old('front_heading_font_family', $front['heading_font_family'] ?? ($front['font_family'] ?? 'Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif')) }}"></div>
                    <div class="wc-field"><label>Base font size</label><input name="front_base_font_size" value="{{ old('front_base_font_size', $front['base_font_size'] ?? '15px') }}" placeholder="15px"></div>
                    <div class="wc-field"><label>Title size</label><input name="front_title_size" value="{{ old('front_title_size', $front['title_size'] ?? 'clamp(31px,4vw,54px)') }}" placeholder="clamp(31px,4vw,54px)"></div>
                    <div class="wc-field"><label>Container width</label><input name="front_container_width" value="{{ old('front_container_width', $front['container_width'] ?? '1320px') }}" placeholder="1320px"></div>
                    <div class="wc-field"><label>Border radius</label><input name="front_border_radius" value="{{ old('front_border_radius', $front['border_radius'] ?? '8px') }}" placeholder="8px"></div>
                    <div class="wc-field"><label>Image background</label><input type="color" name="front_image_background" value="{{ old('front_image_background', $front['image_background'] ?? '#ffffff') }}"></div>
                    <div class="wc-field"><label>Image fit mode</label><select name="front_image_fit"><option value="contain" @selected(old('front_image_fit', $front['image_fit'] ?? 'contain') === 'contain')>Contain — show complete image</option><option value="cover" @selected(old('front_image_fit', $front['image_fit'] ?? 'contain') === 'cover')>Cover — crop to fill</option></select></div>
                    <div class="wc-field"><label>Protect resource downloads</label><select name="front_hide_downloads"><option value="1" @selected((string) old('front_hide_downloads', $front['hide_downloads'] ?? 1) === '1')>Yes</option><option value="0" @selected((string) old('front_hide_downloads', $front['hide_downloads'] ?? 1) === '0')>No</option></select></div>
                    <div class="wc-field" style="grid-column:1/-1"><label>Intro text</label><textarea name="front_intro_text" rows="3" placeholder="Short public description for this store">{{ old('front_intro_text', $front['intro_text'] ?? '') }}</textarea></div>
                </div>
            </div>
            <div class="wc-form-panel"><div class="wc-form-panel-head"><span class="wc-form-panel-icon"><i class="fa-solid fa-code"></i></span><div><h4>Advanced metadata</h4><p class="wc-muted">Optional JSON for future extensions.</p></div></div><div class="wc-field"><label>Metadata JSON</label><textarea name="metadata" rows="6">{{ old('metadata', isset($item->metadata) ? json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea></div></div>
        </form>
    </div>
    <aside class="wc-preview-panel">
        <div class="wc-live-preview" id="wcStoreLivePreview">
            <div class="wc-live-preview-header" data-preview-header>
                <div class="wc-live-preview-brand">
                    <span class="wc-live-preview-mark">
                        @if(!empty($item?->logo_path))
                            <img src="{{ str_starts_with($item->logo_path, 'http') ? $item->logo_path : asset('storage/' . ltrim($item->logo_path, '/')) }}" alt="Logo">
                        @else
                            WC
                        @endif
                    </span>
                    <span data-preview-name>{{ old('name', $item->name ?? 'Store preview') }}</span>
                </div>
                <div class="wc-live-preview-nav"><span></span><span></span><span></span></div>
            </div>
            <div class="wc-live-preview-body" data-preview-body>
                <div class="wc-live-preview-hero">
                    <div class="wc-live-preview-copy" data-preview-surface>
                        <small data-preview-layout>{{ old('front_layout', $front['layout'] ?? 'classic_catalogue') }}</small>
                        <h3 data-preview-title>{{ old('name', $item->name ?? 'Premium catalogue') }}</h3>
                        <p data-preview-intro>{{ old('front_intro_text', $front['intro_text'] ?? 'A configurable visual catalogue experience for your clients.') }}</p>
                    </div>
                    <div class="wc-live-preview-media" data-preview-image-bg><i class="fa-solid fa-cube"></i></div>
                </div>
                <div class="wc-live-preview-grid">
                    <div class="wc-live-preview-product" data-preview-surface><div class="wc-live-preview-img" data-preview-image-bg><i class="fa-solid fa-image"></i></div><div><strong>Product Card</strong><span>3D · AR · Details</span></div></div>
                    <div class="wc-live-preview-product" data-preview-surface><div class="wc-live-preview-img" data-preview-image-bg><i class="fa-solid fa-vr-cardboard"></i></div><div><strong>Immersive</strong><span>Viewer ready</span></div></div>
                    <div class="wc-live-preview-product" data-preview-surface><div class="wc-live-preview-img" data-preview-image-bg><i class="fa-solid fa-file-lines"></i></div><div><strong>Resources</strong><span>Docs & media</span></div></div>
                </div>
            </div>
        </div>
        <div class="wc-preview-links">
            @if(!empty($item?->slug))
                <a class="wc-secondary-btn" target="_blank" href="{{ route('webcatalogue.front.store.show', $item->slug) }}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Open front</a>
                <a class="wc-secondary-btn" target="_blank" href="{{ route('webcatalogue.front.scan.index', $item->slug) }}"><i class="fa-solid fa-camera"></i> Scan page</a>
            @endif
        </div>
        <div class="wc-preview-card"><div class="wc-preview-body"><h4>Configuration summary</h4><p class="wc-muted"><span data-preview-code>{{ old('code', $item->code ?? 'CODE') }}</span> · <span data-preview-domain>{{ old('domain', $item->domain ?? 'domain') }}</span></p><span class="wc-badge">{{ old('status', $item->status ?? 'active') }}</span></div></div>
    </aside>
</div>
</div>

<script>
(function(){
    const preview = document.getElementById('wcStoreLivePreview');
    if (!preview) return;
    const $ = (sel) => document.querySelector(sel);
    const setText = (sel, value) => { const el = document.querySelector(sel); if (el) el.textContent = value || ''; };
    const field = (name) => document.querySelector('[name="'+name+'"]');
    const presets = {
        minimal: {front_primary_color:'#111827',front_secondary_color:'#ffffff',front_header_color:'#ffffff',front_header_text_color:'#111827',front_background_color:'#f8fafc',front_surface_color:'#ffffff',front_text_color:'#111827',front_muted_text_color:'#64748b',front_image_background:'#ffffff',front_border_radius:'5px'},
        premium: {front_primary_color:'#d4af37',front_secondary_color:'#f7d76c',front_header_color:'#0f172a',front_header_text_color:'#ffffff',front_background_color:'#111827',front_surface_color:'#1f2937',front_text_color:'#f8fafc',front_muted_text_color:'#cbd5e1',front_image_background:'#0b1220',front_border_radius:'5px'},
        gold: {front_primary_color:'#b68b2d',front_secondary_color:'#f1d28a',front_header_color:'#121212',front_header_text_color:'#ffffff',front_background_color:'#f5efe3',front_surface_color:'#ffffff',front_text_color:'#151923',front_muted_text_color:'#6b7280',front_image_background:'#fffaf0',front_border_radius:'5px'},
        technical: {front_primary_color:'#2563eb',front_secondary_color:'#93c5fd',front_header_color:'#0b1220',front_header_text_color:'#ffffff',front_background_color:'#eef2f7',front_surface_color:'#ffffff',front_text_color:'#0f172a',front_muted_text_color:'#475569',front_image_background:'#ffffff',front_border_radius:'5px'}
    };
    function updatePreview(){
        const header = $('[data-preview-header]');
        const body = $('[data-preview-body]');
        const surfaces = document.querySelectorAll('[data-preview-surface]');
        const imageBgs = document.querySelectorAll('[data-preview-image-bg]');
        const primary = field('front_primary_color')?.value || '#b68b2d';
        const headerColor = field('front_header_color')?.value || '#ffffff';
        const headerText = field('front_header_text_color')?.value || '#151923';
        const bg = field('front_background_color')?.value || '#f6f7fb';
        const surface = field('front_surface_color')?.value || '#ffffff';
        const text = field('front_text_color')?.value || '#151923';
        const muted = field('front_muted_text_color')?.value || '#687386';
        const imageBg = field('front_image_background')?.value || '#ffffff';
        const radius = field('front_border_radius')?.value || '5px';
        if (header) { header.style.background = headerColor; header.style.color = headerText; }
        if (body) { body.style.background = bg; body.style.color = text; }
        preview.style.borderRadius = radius;
        surfaces.forEach(el => { el.style.background = surface; el.style.borderRadius = radius; el.style.color = text; });
        imageBgs.forEach(el => { el.style.background = imageBg; el.style.color = primary; });
        setText('[data-preview-name]', field('name')?.value || 'Store preview');
        setText('[data-preview-title]', field('name')?.value || 'Premium catalogue');
        setText('[data-preview-code]', field('code')?.value || 'CODE');
        setText('[data-preview-domain]', field('domain')?.value || 'domain');
        setText('[data-preview-layout]', field('front_layout')?.value || 'classic_catalogue');
        setText('[data-preview-intro]', field('front_intro_text')?.value || 'A configurable visual catalogue experience for your clients.');
    }
    document.querySelectorAll('input,select,textarea').forEach(el => el.addEventListener('input', updatePreview));
    document.querySelectorAll('[data-wc-preset]').forEach(btn => btn.addEventListener('click', function(){
        const p = presets[this.dataset.wcPreset] || {};
        Object.keys(p).forEach(name => { const el = field(name); if (el) el.value = p[name]; });
        updatePreview();
    }));
    updatePreview();
})();
</script>

@endsection
