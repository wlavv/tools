<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Storage;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Models\DocumentVersion;
use RuntimeException;

class DocumentOcrService
{
    public function __construct(private readonly AiGatewayService $gateway)
    {
    }

    public function processDocument(Document $document, array $options = []): array
    {
        $version = $options['version'] ?? $this->currentVersion($document);

        if (!$version instanceof DocumentVersion) {
            throw new RuntimeException('Document does not have a file version available for OCR.');
        }

        $type = $this->detectDocumentType($document, $version);
        $path = $this->getDocumentAbsolutePath($version);

        $result = match ($type) {
            'image' => $this->gateway->ocrImage($path, $options),
            'pdf' => $this->gateway->ocrPdf($path, $options),
            default => throw new RuntimeException('Document type is not supported for OCR.'),
        };

        return [
            'status' => data_get($result, 'status', 'ok'),
            'type' => $type,
            'text' => (string) data_get($result, 'result.text', data_get($result, 'text', '')),
            'raw_text' => (string) data_get($result, 'result.raw_text', data_get($result, 'raw_text', '')),
            'language' => (string) data_get($result, 'result.language', $options['lang'] ?? 'por+eng'),
            'preprocess' => (bool) data_get($result, 'result.preprocess', $options['preprocess'] ?? true),
            'llm_ready' => (bool) data_get($result, 'result.llm_ready', false),
            'text_length' => (int) data_get($result, 'result.text_length', mb_strlen((string) data_get($result, 'result.text', ''))),
            'processing_time_ms' => data_get($result, 'result.processing_time_ms'),
            'pages_processed' => data_get($result, 'result.pages_processed', data_get($result, 'result.pages')),
            'payload' => $result,
        ];
    }

    public function supportsDocument(Document $document): bool
    {
        $version = $this->currentVersion($document);

        if (!$version) {
            return false;
        }

        return in_array($this->detectDocumentType($document, $version), ['image', 'pdf'], true);
    }

    public function getDocumentAbsolutePath(DocumentVersion $version): string
    {
        $disk = Storage::disk($version->disk);

        if (!$disk->exists($version->path)) {
            throw new RuntimeException('Document file was not found in storage.');
        }

        try {
            return $disk->path($version->path);
        } catch (\Throwable $exception) {
            $stream = $disk->readStream($version->path);

            if (!$stream) {
                throw new RuntimeException('Unable to read document file from storage.', 0, $exception);
            }

            $extension = $version->extension ? '.' . ltrim((string) $version->extension, '.') : '';
            $temporaryPath = tempnam(sys_get_temp_dir(), 'lsg_ocr_') . $extension;
            file_put_contents($temporaryPath, stream_get_contents($stream));

            if (is_resource($stream)) {
                fclose($stream);
            }

            return $temporaryPath;
        }
    }

    public function detectDocumentType(Document $document, ?DocumentVersion $version = null): string
    {
        $version ??= $this->currentVersion($document);
        $mimeType = (string) ($version?->mime_type ?: $document->mime_type);
        $extension = strtolower((string) ($version?->extension ?: $document->extension));

        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        if (str_starts_with($mimeType, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'tif', 'tiff', 'bmp'], true)) {
            return 'image';
        }

        return 'unsupported';
    }

    private function currentVersion(Document $document): ?DocumentVersion
    {
        if ($document->relationLoaded('currentVersion') && $document->currentVersion) {
            return $document->currentVersion;
        }

        if ($document->current_version_id) {
            $version = DocumentVersion::query()->find($document->current_version_id);

            if ($version) {
                return $version;
            }
        }

        return DocumentVersion::query()
            ->where('document_id', $document->id)
            ->orderByDesc('version_number')
            ->orderByDesc('id')
            ->first();
    }
}
