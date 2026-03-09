@extends('layouts.app')

@section('content')
    @include('asset-library::Includes.css')

    <div class="al-page">
        @include('asset-library::Includes._components.flash')

        <div class="al-show-grid">
            <div class="al-show-preview al-panel">
                <div class="al-show-media">
                    @if($asset->type === 'image' && $previewUrl)
                        <img src="{{ $previewUrl }}" alt="{{ $asset->alt_text ?: $asset->name }}">
                    @elseif($asset->type === 'video' && $previewUrl)
                        <video controls preload="metadata">
                            <source src="{{ $previewUrl }}" type="{{ $asset->mime_type }}">
                        </video>
                    @else
                        <div class="al-card-preview">
                            <span class="al-muted">{{ strtoupper($asset->extension ?: $asset->type) }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="al-show-card al-panel">
                <div class="al-show-meta">
                    <div class="al-kv"><div>Nome</div><div>{{ $asset->name }}</div></div>
                    <div class="al-kv"><div>Tipo</div><div>{{ ucfirst($asset->type) }}</div></div>
                    <div class="al-kv"><div>Estado</div><div>{{ ucfirst($asset->status) }}</div></div>
                    <div class="al-kv"><div>Slug</div><div><span class="al-code">{{ $asset->slug }}</span></div></div>
                    <div class="al-kv"><div>Ficheiro</div><div>{{ $asset->original_filename ?: $asset->filename }}</div></div>
                    <div class="al-kv"><div>MIME</div><div>{{ $asset->mime_type ?: '-' }}</div></div>
                    <div class="al-kv"><div>Tamanho</div><div>{{ number_format(($asset->file_size ?? 0) / 1024, 1) }} KB</div></div>
                    <div class="al-kv"><div>Path</div><div><span class="al-code">{{ $asset->file_path }}</span></div></div>
                    <div class="al-kv"><div>Tags</div><div>{{ $asset->tags ?: '-' }}</div></div>
                    <div class="al-kv"><div>Público</div><div>{{ $asset->is_public ? 'Sim' : 'Não' }}</div></div>
                    <div class="al-kv"><div>Descrição</div><div>{{ $asset->description ?: '-' }}</div></div>
                </div>

                <div class="al-actions" style="margin-top:1rem;">
                    <a href="{{ route('asset_library.edit', $asset) }}" class="btn btn-primary">Editar</a>
                    <a href="{{ route('asset_library.download', $asset) }}" class="btn btn-outline-secondary">Download</a>
                    <a href="{{ route('asset_library.index') }}" class="btn btn-outline-secondary">Voltar</a>

                    <form method="POST" action="{{ route('asset_library.destroy', $asset) }}" onsubmit="return confirm('Remover este asset?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">Apagar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
