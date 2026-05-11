@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-document-hero">
        <div>
            <span class="dms-eyebrow">Preview engine</span>
            <h2>{{ $document->title }}</h2>
            <p>{{ $mimeType ?: 'Sem mime type registado.' }}</p>
        </div>
    </div>

    @include('documentmanager::documents.partials.operations', ['document' => $document])

    <div class="dms-card">
        <div class="dms-card__head">
            <div>
                <span class="dms-eyebrow">Inline preview</span>
                <h3>{{ $version?->original_name ?: 'Documento' }}</h3>
            </div>
        </div>

        <div class="dms-preview dms-preview--large">
            @if(!$version)
                <i class="fa-regular fa-file"></i>
                <strong>Sem versao de ficheiro</strong>
                <span>Este documento ainda nao tem ficheiro associado.</span>
            @elseif(!$canPreview)
                <i class="fa-solid fa-file-circle-question"></i>
                <strong>Preview nao disponivel</strong>
                <span>O ficheiro existe, mas o formato nao tem preview inline ou nao esta acessivel no storage.</span>
                @if($downloadUrl)
                    <a href="{{ $downloadUrl }}" class="btn btn-outline-primary">
                        <i class="fa-solid fa-download"></i> Download
                    </a>
                @endif
            @elseif(str_starts_with((string) $mimeType, 'image/'))
                <img src="{{ $previewUrl }}" alt="{{ $document->title }}" class="dms-preview-image dms-preview-image--large">
            @elseif(str_starts_with((string) $mimeType, 'video/'))
                <video class="dms-preview-video dms-preview-video--large" controls autoplay muted preload="metadata">
                    <source src="{{ $previewUrl }}" type="{{ $mimeType }}">
                </video>
            @else
                <iframe class="dms-preview-frame dms-preview-frame--large" src="{{ $previewUrl }}" title="Preview {{ $document->title }}"></iframe>
            @endif
        </div>
    </div>
@endsection
