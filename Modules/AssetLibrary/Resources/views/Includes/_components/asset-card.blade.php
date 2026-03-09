@php
    $url = $asset->file_path ? Storage::disk($asset->disk ?: config('asset-library.disk', 'public'))->url($asset->file_path) : null;
@endphp

<div class="al-card">
    <div class="al-card-preview">
        @if($asset->type === 'image' && $url)
            <img src="{{ $url }}" alt="{{ $asset->alt_text ?: $asset->name }}">
        @elseif($asset->type === 'video' && $url)
            <span class="al-muted">Vídeo</span>
        @elseif($asset->type === 'model' && $url)
            <span class="al-muted">Modelo 3D</span>
        @elseif($asset->type === 'document' && $url)
            <span class="al-muted">Documento</span>
        @else
            <span class="al-muted">{{ strtoupper($asset->extension ?: 'FILE') }}</span>
        @endif
    </div>

    <div class="al-card-meta">
        <h3 class="al-card-title">{{ $asset->name }}</h3>
        <div>
            <span class="al-badge">{{ ucfirst($asset->type) }}</span>
            <span class="al-badge">{{ ucfirst($asset->status) }}</span>
        </div>
        <div class="al-muted">{{ $asset->original_filename ?: $asset->filename }}</div>
        <div class="al-muted">{{ number_format(($asset->file_size ?? 0) / 1024, 1) }} KB</div>
    </div>

    <div class="al-actions">
        <a href="{{ route('asset_library.show', $asset) }}" class="btn btn-outline-secondary btn-sm">Ver</a>
        <a href="{{ route('asset_library.edit', $asset) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
        <a href="{{ route('asset_library.download', $asset) }}" class="btn btn-outline-secondary btn-sm">Download</a>
    </div>
</div>
