<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class AuditService
{
    public function activity(?int $documentId, string $event, array $payload = [], ?int $userId = null): void
    {
        if (!DocumentTable::exists('document_logs_activity')) {
            return;
        }

        try {
            DB::table('document_logs_activity')->insert([
                'uuid' => (string) Str::uuid(),
                'document_id' => $documentId,
                'event' => $event,
                'actor_type' => $userId ? 'user' : null,
                'actor_id' => $userId,
                'ip_address' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'payload' => json_encode($payload, JSON_UNESCAPED_SLASHES),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['event' => $event, 'document_id' => $documentId]);
        }
    }
}
