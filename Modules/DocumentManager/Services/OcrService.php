<?php

namespace Modules\DocumentManager\Services;

use App\Services\AI\DocumentOcrService as LsgDocumentOcrService;
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
        private AuditService $audit,
        private LsgDocumentOcrService $lsgOcr
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
            $result = $this->extract($document, $version);
            $completed = in_array($result['status'], ['completed', 'ok'], true) && trim((string) ($result['text'] ?? '')) !== '';

            if (DocumentTable::exists('document_ai_ocr')) {
                DB::table('document_ai_ocr')
                    ->where('id', $ocrId)
                    ->update([
                        'status' => $completed ? 'completed' : $result['status'],
                        'confidence' => $result['confidence'],
                        'extracted_text' => $result['text'],
                        'structured_blocks' => json_encode($result['structured_blocks'] ?? [
                            ['type' => 'text', 'text' => $result['text']],
                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        'raw_response' => json_encode($result['raw_response'] ?? [
                            'provider' => $this->provider(),
                            'message' => $result['message'],
                        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
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
        if ($this->provider() === 'lsg_ai') {
            try {
                $health = app(\App\Services\AI\AiGatewayService::class)->health();

                return [
                    'provider' => $this->provider(),
                    'ok' => ($health['status'] ?? null) === 'ok',
                    'message' => ($health['service'] ?? 'LSG AI Gateway') . ' / OCR endpoints configured',
                ];
            } catch (\Throwable $e) {
                return [
                    'provider' => $this->provider(),
                    'ok' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

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

    private function extract(Document $document, DocumentVersion $version): array
    {
        if ($this->provider() !== 'lsg_ai') {
            return $this->textExtraction->extract($version);
        }

        try {
            $result = $this->lsgOcr->processDocument($document, [
                'version' => $version,
                'lang' => 'por+eng',
                'preprocess' => true,
                'max_pages' => 5,
            ]);

            return [
                'text' => $result['text'],
                'status' => trim($result['text']) !== '' ? 'completed' : 'empty',
                'message' => trim($result['text']) !== '' ? null : 'OCR completed without extracted text.',
                'confidence' => $result['llm_ready'] ? 0.8500 : null,
                'structured_blocks' => [
                    [
                        'type' => 'text',
                        'text' => $result['text'],
                        'language' => $result['language'],
                        'text_length' => $result['text_length'],
                        'processing_time_ms' => $result['processing_time_ms'],
                        'pages_processed' => $result['pages_processed'],
                    ],
                ],
                'raw_response' => [
                    'provider' => $this->provider(),
                    'engine' => 'LSG AI Gateway OCR',
                    'type' => $result['type'],
                    'language' => $result['language'],
                    'preprocess' => $result['preprocess'],
                    'llm_ready' => $result['llm_ready'],
                    'text_length' => $result['text_length'],
                    'processing_time_ms' => $result['processing_time_ms'],
                    'pages_processed' => $result['pages_processed'],
                    'payload' => $result['payload'],
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'text' => '',
                'status' => 'failed',
                'message' => $e->getMessage(),
                'confidence' => null,
                'structured_blocks' => [],
                'raw_response' => [
                    'provider' => $this->provider(),
                    'engine' => 'LSG AI Gateway OCR',
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }
}
