<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class RelationService
{
    public function attach(int $documentId, string $relationType, string $relatedType, ?int $relatedId = null, string $source = 'manual'): void
    {
        if (!DocumentTable::exists('document_core_relations')) {
            return;
        }

        try {
            DB::table('document_core_relations')->insert([
                'uuid' => (string) Str::uuid(),
                'document_id' => $documentId,
                'relation_type' => $relationType,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'source' => $source,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $documentId]);
        }
    }
}
