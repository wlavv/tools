<?php

namespace Modules\DocumentManager\Services;

class PreviewService
{
    public function supportedMimeTypes(): array
    {
        return [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/webp',
            'text/plain',
            'text/markdown',
            'text/html',
            'video/mp4',
        ];
    }

    public function canPreview(?string $mimeType): bool
    {
        return $mimeType && in_array($mimeType, $this->supportedMimeTypes(), true);
    }
}
