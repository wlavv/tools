<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class EmbeddingService
{
    public function provider(): string
    {
        return (string) config('documentmanager.providers.embeddings', 'stub');
    }

    public function queue(int $documentId, ?int $versionId = null): void
    {
        if (!DocumentTable::exists('document_ai_embeddings')) {
            return;
        }

        try {
            DB::table('document_ai_embeddings')->insert([
                'uuid' => (string) Str::uuid(),
                'document_id' => $documentId,
                'version_id' => $versionId,
                'provider' => $this->provider(),
                'model' => 'pending',
                'metadata' => json_encode(['status' => 'queued'], JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $documentId]);
        }
    }

    public function process(int $documentId, ?int $versionId = null): void
    {
        if (!DocumentTable::exists('document_core_documents') || !DocumentTable::exists('document_ai_embeddings')) {
            return;
        }

        try {
            $document = Document::query()->find($documentId);

            if (!$document) {
                return;
            }

            $content = trim((string) $document->search_text ?: $document->title);
            $hash = hash('sha256', $content);

            DB::table('document_ai_embeddings')->updateOrInsert(
                ['document_id' => $documentId, 'provider' => $this->provider(), 'content_hash' => $hash],
                [
                    'uuid' => (string) Str::uuid(),
                    'version_id' => $versionId,
                    'model' => $this->provider() === 'stub' ? 'local-hash-v1' : null,
                    'vector_store' => 'local',
                    'vector_id' => $hash,
                    'dimensions' => 0,
                    'embedding_payload' => null,
                    'metadata' => json_encode([
                        'status' => 'completed',
                        'mode' => 'hash-placeholder',
                    ], JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $document->update(['has_embeddings' => true]);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $documentId]);
        }
    }
}
