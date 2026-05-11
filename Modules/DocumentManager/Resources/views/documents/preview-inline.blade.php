@php
    $previewMime = $currentVersion?->mime_type ?: $document->mime_type;
    $hasInlineFile = ($fileAvailable ?? false) && $currentVersion;
    $fileUrl = $hasInlineFile ? route('document-manager.documents.file', $document->id) : null;
    $previewUrl = $hasInlineFile ? route('document-manager.documents.preview', $document->id) : null;
    $downloadUrl = $hasInlineFile ? route('document-manager.documents.download', $document->id) : null;
    $isImagePreview = $fileUrl && str_starts_with((string) $previewMime, 'image/');
    $isVideoPreview = $fileUrl && str_starts_with((string) $previewMime, 'video/');
    $isFramePreview = $fileUrl && in_array($previewMime, ['application/pdf', 'text/plain', 'text/markdown', 'text/html'], true);
    $previewClass = !empty($compact) ? 'dms-document-sheet dms-document-sheet--edit' : 'dms-document-sheet';
@endphp

<div class="dms-preview {{ $previewClass }}">
    @if($isImagePreview)
        <a href="{{ $previewUrl }}" class="dms-preview-link">
            <img src="{{ $fileUrl }}" alt="{{ $document->title }}" class="dms-preview-image dms-preview-image--sheet">
        </a>
    @elseif($isVideoPreview)
        <video class="dms-preview-video dms-preview-video--sheet" controls preload="metadata">
            <source src="{{ $fileUrl }}" type="{{ $previewMime }}">
        </video>
    @elseif($isFramePreview)
        <iframe class="dms-preview-frame dms-preview-frame--sheet" src="{{ $fileUrl }}" title="Preview {{ $document->title }}"></iframe>
    @elseif($document->has_file && $currentVersion && !($fileAvailable ?? false))
        <i class="fa-solid fa-triangle-exclamation"></i>
        <strong>Ficheiro indisponivel no storage</strong>
        <span>A versao existe na base de dados, mas o ficheiro nao foi encontrado no disco configurado.</span>
    @elseif($document->has_file)
        <i class="fa-solid fa-file-circle-question"></i>
        <strong>Preview nao suportado neste formato</strong>
        <span>O ficheiro existe e pode ser descarregado.</span>
        @if($downloadUrl)
            <a href="{{ $downloadUrl }}" class="btn btn-outline-primary btn-sm">
                <i class="fa-solid fa-download"></i> Download
            </a>
        @endif
    @else
        <i class="fa-regular fa-file"></i>
        <strong>Sem ficheiro</strong>
        <span>Este documento existe como objeto operacional sem anexo fisico.</span>
    @endif
</div>
