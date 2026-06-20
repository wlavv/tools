<?php

namespace App\Console\Commands;

use App\Services\AI\AiGatewayService;
use App\Services\AI\DocumentAiService;
use Illuminate\Console\Command;
use Modules\DocumentManager\Models\Document;
use Throwable;

class TestAiExtractExpense extends Command
{
    protected $signature = 'lsg:ai-extract-expense {document_id?} {--file=}';

    protected $description = 'Test LSG AI expense extraction using a Document Manager document or a local file path.';

    public function handle(AiGatewayService $gateway, DocumentAiService $documents): int
    {
        try {
            $file = $this->option('file');

            $result = $file
                ? $this->processFile($gateway, $documents, (string) $file)
                : $this->processDocument($documents);

            $expense = $result['expense'] ?? [];

            $this->table(['Field', 'Value'], [
                ['AI Gateway', strtoupper((string) ($result['status'] ?? 'unknown'))],
                ['File', (string) ($result['file'] ?? 'Document #' . $this->argument('document_id'))],
                ['OCR text length', (string) ($result['text_length'] ?? 0)],
                ['Processing time', (string) ($result['processing_time_ms'] ?? '-') . ' ms'],
                ['Supplier', (string) ($expense['supplier_name'] ?? '-')],
                ['Total', trim((string) ($expense['total'] ?? '-') . ' ' . (string) ($expense['currency'] ?? 'EUR'))],
                ['Category suggestion', (string) ($expense['category_suggestion'] ?? '-')],
                ['Confidence', (string) ($expense['confidence'] ?? '-')],
                ['Notes', (string) ($expense['notes'] ?? '-')],
            ]);

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function processDocument(DocumentAiService $documents): array
    {
        $documentId = $this->argument('document_id');

        if (!$documentId) {
            throw new \InvalidArgumentException('Provide a document_id or --file path.');
        }

        $document = Document::query()->findOrFail((int) $documentId);

        return $documents->extractExpenseFromDocument($document);
    }

    private function processFile(AiGatewayService $gateway, DocumentAiService $documents, string $filePath): array
    {
        $response = $gateway->extractExpense($filePath);
        $result = $documents->normalizeExpensePayload($response);

        return array_merge($result, [
            'file' => $filePath,
        ]);
    }
}
