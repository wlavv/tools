<?php

namespace App\Console\Commands;

use App\Services\AI\AiGatewayService;
use App\Services\AI\DocumentOcrService;
use Illuminate\Console\Command;
use Modules\DocumentManager\Models\Document;
use Throwable;

class TestDocumentOcr extends Command
{
    protected $signature = 'lsg:ocr-test {document_id?} {--file=}';

    protected $description = 'Test LSG AI OCR using a Document Manager document or a local file path.';

    public function handle(AiGatewayService $gateway, DocumentOcrService $documents): int
    {
        try {
            $file = $this->option('file');

            $result = $file
                ? $this->processFile($gateway, (string) $file)
                : $this->processDocument($documents);

            $text = (string) ($result['text'] ?? data_get($result, 'result.text', ''));

            $this->table(['Field', 'Value'], [
                ['status', (string) ($result['status'] ?? 'unknown')],
                ['type', (string) ($result['type'] ?? data_get($result, 'result.type', 'unknown'))],
                ['text_length', (string) ($result['text_length'] ?? data_get($result, 'result.text_length', mb_strlen($text)))],
                ['processing_time_ms', (string) ($result['processing_time_ms'] ?? data_get($result, 'result.processing_time_ms', '-'))],
            ]);

            $this->newLine();
            $this->line(mb_substr(trim($text), 0, 1200) ?: '[sem texto]');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function processDocument(DocumentOcrService $documents): array
    {
        $documentId = $this->argument('document_id');

        if (!$documentId) {
            throw new \InvalidArgumentException('Provide a document_id or --file path.');
        }

        $document = Document::query()->findOrFail((int) $documentId);

        return $documents->processDocument($document);
    }

    private function processFile(AiGatewayService $gateway, string $filePath): array
    {
        $extension = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));

        $response = $extension === 'pdf'
            ? $gateway->ocrPdf($filePath)
            : $gateway->ocrImage($filePath);

        return [
            'status' => (string) data_get($response, 'status', 'unknown'),
            'type' => (string) data_get($response, 'result.type', $extension === 'pdf' ? 'pdf' : 'image'),
            'text' => (string) data_get($response, 'result.text', ''),
            'text_length' => (int) data_get($response, 'result.text_length', 0),
            'processing_time_ms' => data_get($response, 'result.processing_time_ms'),
            'payload' => $response,
        ];
    }
}
