<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Models\DocumentVersion;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class OcrService
{
    public function __construct(
        private TextExtractionService $textExtraction,
        private AuditService $audit
    ) {
    }

    public function provider(): string
    {
        return (string) config('documentmanager.providers.ocr', 'stub');
    }

    public function queue(int $documentId, ?int $versionId = null): void
    {
        if (!DocumentTable::exists('document_ai_ocr')) {
            return;
        }

        try {
            DB::table('document_ai_ocr')->insert([
                'uuid' => (string) Str::uuid(),
                'document_id' => $documentId,
                'version_id' => $versionId,
                'provider' => $this->provider(),
                'status' => 'queued',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $documentId]);
        }
    }

    public function process(int $documentId, ?int $versionId = null): array
    {
        if (!DocumentTable::exists('document_core_documents') || !DocumentTable::exists('document_core_versions')) {
            return ['status' => 'skipped', 'text' => ''];
        }

        try {
            $document = Document::query()->find($documentId);

            if (!$document) {
                return ['status' => 'missing_document', 'text' => ''];
            }

            $version = $versionId
                ? DocumentVersion::query()->find($versionId)
                : DocumentVersion::query()
                    ->where('document_id', $documentId)
                    ->orderByDesc('version_number')
                    ->orderByDesc('id')
                    ->first();

            if (!$version) {
                return ['status' => 'missing_version', 'text' => ''];
            }

            $ocrId = $this->startOcr($documentId, $version->id);
            $result = $this->textExtraction->extract($version);
            $completed = $result['status'] === 'completed';

            if (DocumentTable::exists('document_ai_ocr')) {
                DB::table('document_ai_ocr')
                    ->where('id', $ocrId)
                    ->update([
                        'status' => $completed ? 'completed' : $result['status'],
                        'confidence' => $result['confidence'],
                        'extracted_text' => $result['text'],
                        'structured_blocks' => json_encode([
                            ['type' => 'text', 'text' => $result['text']],
                        ], JSON_UNESCAPED_SLASHES),
                        'raw_response' => json_encode([
                            'provider' => $this->provider(),
                            'message' => $result['message'],
                        ], JSON_UNESCAPED_SLASHES),
                        'error_message' => $completed ? null : $result['message'],
                        'finished_at' => now(),
                        'updated_at' => now(),
                    ]);
            }

            $searchText = trim($document->title . ' ' . (string) $document->description . ' ' . $result['text']);

            $document->update([
                'has_ocr' => $completed,
                'search_text' => $searchText,
            ]);

            $version->update([
                'processing_status' => $completed ? 'ocr_completed' : 'ocr_' . $result['status'],
            ]);

            $this->audit->activity($documentId, 'ocr.' . ($completed ? 'completed' : $result['status']), [
                'version_id' => $version->id,
                'provider' => $this->provider(),
                'characters' => mb_strlen($result['text']),
            ]);

            return $result;
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $documentId, 'version_id' => $versionId]);

            return ['status' => 'failed', 'text' => '', 'message' => $e->getMessage()];
        }
    }

    public function health(): array
    {
        return [
            'provider' => $this->provider(),
            'ok' => true,
            'message' => $this->provider() === 'stub'
                ? 'OCR provider stub configured'
                : 'OCR provider configured',
        ];
    }

    private function startOcr(int $documentId, ?int $versionId): ?int
    {
        if (!DocumentTable::exists('document_ai_ocr')) {
            return null;
        }

        return DB::table('document_ai_ocr')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'document_id' => $documentId,
            'version_id' => $versionId,
            'provider' => $this->provider(),
            'status' => 'processing',
            'started_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
