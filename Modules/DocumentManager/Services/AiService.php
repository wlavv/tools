<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class AiService
{
    public function provider(): string
    {
        return (string) config('documentmanager.providers.ai', 'stub');
    }

    public function log(?int $documentId, string $operation, string $status, array $context = [], ?string $message = null): void
    {
        if (!DocumentTable::exists('document_logs_ai')) {
            return;
        }

        try {
            DB::table('document_logs_ai')->insert([
                'uuid' => (string) Str::uuid(),
                'document_id' => $documentId,
                'provider' => $this->provider(),
                'operation' => $operation,
                'status' => $status,
                'context' => json_encode($context, JSON_UNESCAPED_SLASHES),
                'message' => $message,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['operation' => $operation]);
        }
    }

    public function classify(int $documentId): void
    {
        $this->analyze($documentId);
    }

    public function summarize(int $documentId, ?string $text = null): ?string
    {
        if (!DocumentTable::exists('document_core_documents') || !DocumentTable::exists('document_ai_summaries')) {
            return null;
        }

        try {
            $document = Document::query()->find($documentId);

            if (!$document) {
                return null;
            }

            $sourceText = trim($text ?: (string) $document->search_text ?: (string) $document->description);
            $summary = $this->buildSummary($document->title, $sourceText);
            $keywords = $this->extractKeywords($sourceText ?: $document->title);

            DB::table('document_ai_summaries')->updateOrInsert(
                ['document_id' => $documentId, 'summary_type' => 'executive'],
                [
                    'uuid' => (string) Str::uuid(),
                    'provider' => $this->provider(),
                    'model' => $this->provider() === 'stub' ? 'local-summary-v1' : null,
                    'summary' => $summary,
                    'keywords' => json_encode($keywords, JSON_UNESCAPED_SLASHES),
                    'entities' => json_encode($this->extractEntities($sourceText), JSON_UNESCAPED_SLASHES),
                    'confidence' => $sourceText !== '' ? 0.6500 : 0.2500,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $this->log($documentId, 'summary', 'completed', [
                'provider' => $this->provider(),
                'characters' => mb_strlen($sourceText),
            ], 'Resumo executivo gerado.');

            return $summary;
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $documentId]);
            $this->log($documentId, 'summary', 'failed', [], $e->getMessage());

            return null;
        }
    }

    public function analyze(int $documentId, ?string $text = null): void
    {
        if (!DocumentTable::exists('document_core_documents') || !DocumentTable::exists('document_ai_analysis')) {
            return;
        }

        try {
            $document = Document::query()->find($documentId);

            if (!$document) {
                return;
            }

            $sourceText = trim($text ?: (string) $document->search_text ?: (string) $document->description);
            $classification = $this->classifyText($document->title, $sourceText, $document->document_type);
            $riskFlags = $this->riskFlags($sourceText);

            DB::table('document_ai_analysis')->updateOrInsert(
                ['document_id' => $documentId, 'analysis_type' => 'baseline'],
                [
                    'uuid' => (string) Str::uuid(),
                    'provider' => $this->provider(),
                    'model' => $this->provider() === 'stub' ? 'local-analysis-v1' : null,
                    'status' => 'completed',
                    'confidence' => $sourceText !== '' ? 0.5500 : 0.2500,
                    'classification' => json_encode($classification, JSON_UNESCAPED_SLASHES),
                    'risk_flags' => json_encode($riskFlags, JSON_UNESCAPED_SLASHES),
                    'relation_suggestions' => json_encode([], JSON_UNESCAPED_SLASHES),
                    'raw_payload' => json_encode(['source' => 'local-baseline'], JSON_UNESCAPED_SLASHES),
                    'error_message' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $this->log($documentId, 'analysis', 'completed', [
                'classification' => $classification,
                'risk_flags' => $riskFlags,
            ], 'Analise baseline gerada.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $documentId]);
            $this->log($documentId, 'analysis', 'failed', [], $e->getMessage());
        }
    }

    public function health(): array
    {
        return [
            'provider' => $this->provider(),
            'ok' => true,
            'message' => $this->provider() === 'stub'
                ? 'AI provider stub configured'
                : 'AI provider configured',
        ];
    }

    private function buildSummary(string $title, string $text): string
    {
        if ($text === '') {
            return 'Documento "' . $title . '" registado, mas ainda sem texto extraido para resumo automatico.';
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $summary = trim(implode(' ', array_slice($sentences, 0, 3)));

        if ($summary === '') {
            $summary = mb_substr($text, 0, 600);
        }

        return mb_strlen($summary) > 900 ? mb_substr($summary, 0, 900) . '...' : $summary;
    }

    private function extractKeywords(string $text): array
    {
        $words = preg_split('/[^\pL\pN]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $stopWords = ['para', 'com', 'sem', 'uma', 'uns', 'das', 'dos', 'que', 'por', 'and', 'the', 'from', 'this', 'that', 'documento'];
        $counts = [];

        foreach ($words as $word) {
            if (mb_strlen($word) < 4 || in_array($word, $stopWords, true)) {
                continue;
            }

            $counts[$word] = ($counts[$word] ?? 0) + 1;
        }

        arsort($counts);

        return array_slice(array_keys($counts), 0, 12);
    }

    private function extractEntities(string $text): array
    {
        preg_match_all('/\b[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}\b/i', $text, $emails);
        preg_match_all('/\b(?:\+?\d[\d\s().-]{7,}\d)\b/', $text, $phones);
        preg_match_all('/\b\d{4}-\d{2}-\d{2}\b|\b\d{2}\/\d{2}\/\d{4}\b/', $text, $dates);

        return [
            'emails' => array_values(array_unique($emails[0] ?? [])),
            'phones' => array_values(array_unique($phones[0] ?? [])),
            'dates' => array_values(array_unique($dates[0] ?? [])),
        ];
    }

    private function classifyText(string $title, string $text, ?string $documentType): array
    {
        $haystack = mb_strtolower($title . ' ' . $text . ' ' . (string) $documentType);

        $type = match (true) {
            str_contains($haystack, 'invoice') || str_contains($haystack, 'fatura') || str_contains($haystack, 'factura') => 'invoice',
            str_contains($haystack, 'contract') || str_contains($haystack, 'contrato') => 'contract',
            str_contains($haystack, 'receipt') || str_contains($haystack, 'recibo') => 'receipt',
            str_contains($haystack, 'certificate') || str_contains($haystack, 'certificado') => 'certificate',
            str_contains($haystack, 'manual') => 'manual',
            default => $documentType ?: 'document',
        };

        return [
            'document_type' => $type,
            'language' => preg_match('/\b(the|and|invoice|contract)\b/i', $text) ? 'en' : 'pt',
            'source' => 'local-baseline',
        ];
    }

    private function riskFlags(string $text): array
    {
        $haystack = mb_strtolower($text);
        $flags = [];

        foreach (['vencido', 'expired', 'overdue', 'penalty', 'multa', 'urgente', 'confidential', 'confidencial'] as $needle) {
            if (str_contains($haystack, $needle)) {
                $flags[] = $needle;
            }
        }

        return array_values(array_unique($flags));
    }
}
