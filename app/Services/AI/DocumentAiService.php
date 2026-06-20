<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Models\DocumentAiResult;
use Modules\DocumentManager\Models\DocumentOcr;
use Modules\DocumentManager\Models\DocumentVersion;
use Modules\DocumentManager\Services\AuditService;
use RuntimeException;
use Throwable;

class DocumentAiService
{
    private const SUPPORTED_TYPES = ['image', 'pdf'];

    public function __construct(
        private readonly AiGatewayService $gateway,
        private readonly DocumentOcrService $ocrDocuments,
        private readonly AuditService $audit,
    ) {
    }

    public function supportsDocument(Document $document): bool
    {
        return in_array($this->detectDocumentType($document), self::SUPPORTED_TYPES, true);
    }

    public function getDocumentAbsolutePath(Document|DocumentVersion $document): string
    {
        $version = $document instanceof DocumentVersion ? $document : $this->currentVersion($document);

        if (!$version instanceof DocumentVersion) {
            throw new RuntimeException('Document does not have a file version available for AI processing.');
        }

        return $this->ocrDocuments->getDocumentAbsolutePath($version);
    }

    public function extractExpenseFromDocument(Document $document, array $options = []): array
    {
        $version = $this->currentVersion($document);

        if (!$version instanceof DocumentVersion) {
            throw new RuntimeException('Document does not have a file version available for expense extraction.');
        }

        if (!$this->supportsDocument($document)) {
            throw new RuntimeException('Document type is not supported for expense extraction.');
        }

        $path = $this->getDocumentAbsolutePath($version);
        $startedAt = microtime(true);

        try {
            $payload = $this->gateway->extractExpense($path, $options);
            $result = $this->normalizeExpensePayload($payload, $options);

            return DB::transaction(function () use ($document, $version, $result, $payload, $startedAt) {
                $aiResult = $this->storeResult($document, $version, $result, $payload, null, $startedAt);
                $this->storeOcrResult($document, $version, $result, $payload);

                $documentUpdate = ['has_ocr' => true];

                if (Schema::hasColumn($document->getTable(), 'search_text') && $result['text']) {
                    $documentUpdate['search_text'] = $result['text'];
                }

                $document->forceFill($documentUpdate)->save();

                $this->audit->activity($document->id, 'ai.extract_expense.completed', [
                    'ai_result_id' => $aiResult->id,
                    'status' => $aiResult->status,
                    'total' => data_get($result, 'expense.total'),
                ], auth()->id());

                return array_merge($result, [
                    'ai_result_id' => $aiResult->id,
                    'ai_result' => $aiResult,
                ]);
            });
        } catch (Throwable $exception) {
            $error = $this->storeFailure($document, $version, $options, $exception, $startedAt);
            $this->audit->activity($document->id, 'ai.extract_expense.failed', [
                'ai_result_id' => $error->id,
                'message' => $exception->getMessage(),
            ], auth()->id());

            throw new RuntimeException('Nao foi possivel extrair a sugestao de despesa: ' . $exception->getMessage(), 0, $exception);
        }
    }

    public function getLatestAiResult(Document $document, ?string $operation = null): ?DocumentAiResult
    {
        $query = DocumentAiResult::query()
            ->where('document_id', $document->id)
            ->orderByDesc('processed_at')
            ->orderByDesc('id');

        if ($operation) {
            $query->where('operation', $operation);
        }

        return $query->first();
    }

    public function detectDocumentType(Document $document): string
    {
        return $this->ocrDocuments->detectDocumentType($document, $this->currentVersion($document));
    }

    public function normalizeExpensePayload(array $payload, array $options = []): array
    {
        $result = data_get($payload, 'result', []);
        $ocr = data_get($result, 'ocr', []);
        $expense = data_get($result, 'expense', []);

        $text = (string) data_get($ocr, 'text', data_get($ocr, 'raw_text', ''));
        $rawText = (string) data_get($ocr, 'raw_text', $text);

        return [
            'status' => (string) data_get($result, 'status', data_get($payload, 'status', 'ok')),
            'operation' => 'extract_expense',
            'service' => (string) data_get($payload, 'service', 'documents.extract_expense'),
            'document_type' => (string) data_get($result, 'document_type', data_get($ocr, 'type', 'document')),
            'language' => (string) data_get($ocr, 'language', $options['lang'] ?? 'por+eng'),
            'preprocess' => (bool) data_get($ocr, 'preprocess', $options['preprocess'] ?? true),
            'text' => $text,
            'raw_text' => $rawText,
            'text_length' => (int) data_get($ocr, 'text_length', mb_strlen($text)),
            'processing_time_ms' => (int) data_get($ocr, 'processing_time_ms', 0),
            'llm_ready' => (bool) data_get($ocr, 'llm_ready', false),
            'expense' => is_array($expense) ? $expense : [],
            'ocr' => is_array($ocr) ? $ocr : [],
            'llm_raw_response' => data_get($result, 'llm_raw_response'),
            'llm_model' => data_get($result, 'llm_model', config('lsg_ai.default_model')),
            'payload' => $payload,
        ];
    }

    public function expenseFormData(DocumentAiResult $result): array
    {
        $expense = $result->extracted_payload['expense'] ?? [];

        return [
            'supplier_name' => $expense['supplier_name'] ?? null,
            'supplier_vat' => $expense['supplier_vat'] ?? null,
            'invoice_number' => $expense['invoice_number'] ?? null,
            'invoice_date' => $expense['invoice_date'] ?? null,
            'currency' => $expense['currency'] ?? 'EUR',
            'amount_without_tax' => $expense['subtotal'] ?? null,
            'tax_amount' => $expense['tax_amount'] ?? null,
            'amount_with_tax' => $expense['total'] ?? null,
            'category_suggestion' => $expense['category_suggestion'] ?? null,
            'notes' => $expense['notes'] ?? null,
            'document_id' => $result->document_id,
            'ai_result_id' => $result->id,
        ];
    }

    private function storeResult(
        Document $document,
        DocumentVersion $version,
        array $result,
        array $payload,
        ?string $error,
        float $startedAt
    ): DocumentAiResult {
        return DocumentAiResult::query()->create([
            'document_id' => $document->id,
            'version_id' => $version->id,
            'operation' => 'extract_expense',
            'status' => $error ? 'failed' : ($result['status'] ?: 'ok'),
            'service' => $result['service'] ?? 'documents.extract_expense',
            'model' => $result['llm_model'] ?? config('lsg_ai.default_model'),
            'language' => $result['language'] ?? 'por+eng',
            'preprocess' => $result['preprocess'] ?? true,
            'text' => $result['text'] ?? null,
            'raw_text' => $result['raw_text'] ?? null,
            'text_length' => $result['text_length'] ?? null,
            'processing_time_ms' => $result['processing_time_ms'] ?: (int) round((microtime(true) - $startedAt) * 1000),
            'llm_ready' => $result['llm_ready'] ?? false,
            'extracted_payload' => [
                'expense' => $result['expense'] ?? [],
                'ocr' => $result['ocr'] ?? [],
            ],
            'raw_payload' => $payload,
            'raw_llm_response' => $result['llm_raw_response'] ?? null,
            'error_message' => $error,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);
    }

    private function storeOcrResult(Document $document, DocumentVersion $version, array $result, array $payload): void
    {
        DocumentOcr::query()->create([
            'uuid' => (string) Str::uuid(),
            'document_id' => $document->id,
            'version_id' => $version->id,
            'provider' => 'lsg_ai',
            'status' => $result['text'] ? 'completed' : 'empty',
            'confidence' => data_get($result, 'expense.confidence'),
            'extracted_text' => $result['text'] ?? null,
            'structured_blocks' => $result['ocr'] ?? [],
            'raw_response' => $payload,
            'started_at' => now()->subMilliseconds(max(1, (int) ($result['processing_time_ms'] ?? 1))),
            'finished_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function storeFailure(Document $document, ?DocumentVersion $version, array $options, Throwable $exception, float $startedAt): DocumentAiResult
    {
        return DocumentAiResult::query()->create([
            'document_id' => $document->id,
            'version_id' => $version?->id,
            'operation' => 'extract_expense',
            'status' => 'failed',
            'service' => 'documents.extract_expense',
            'model' => config('lsg_ai.default_model'),
            'language' => $options['lang'] ?? 'por+eng',
            'preprocess' => $options['preprocess'] ?? true,
            'processing_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'extracted_payload' => [],
            'raw_payload' => [],
            'error_message' => $exception->getMessage(),
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ]);
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
