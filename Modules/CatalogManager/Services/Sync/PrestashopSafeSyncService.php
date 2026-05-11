<?php

namespace Modules\CatalogManager\Services\Sync;

use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class PrestashopSafeSyncService
{
    public function queue(string $entityType, ?int $entityId, string $operation = 'update', array $payload = []): int
    {
        if (!CatalogTable::exists('catalog_prestashop_sync_queue')) {
            CatalogLogger::warning('Prestashop sync queue table missing.', compact('entityType', 'entityId', 'operation'));

            return 0;
        }

        try {
            return DB::table('catalog_prestashop_sync_queue')->insertGetId([
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'operation' => $operation,
                'status' => 'pending',
                'payload' => json_encode($payload),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, compact('entityType', 'entityId', 'operation'));

            return 0;
        }
    }
}
