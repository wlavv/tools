<?php

namespace Modules\CatalogManager\Services\AI;

use Illuminate\Support\Facades\DB;
use Modules\CatalogManager\Support\CatalogLogger;
use Modules\CatalogManager\Support\CatalogTable;

class AiProductPipelineService
{
    public function createPlaceholderGeneration(?int $productId, string $type, array $input = []): int
    {
        if (!CatalogTable::exists('catalog_ai_generations')) {
            CatalogLogger::warning('AI generations table missing.', compact('productId', 'type'));

            return 0;
        }

        try {
            return DB::table('catalog_ai_generations')->insertGetId([
                'product_id' => $productId,
                'type' => $type,
                'status' => 'generated',
                'input_payload' => json_encode($input),
                'output_payload' => json_encode(['note' => 'Placeholder AI generation.']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, compact('productId', 'type'));

            return 0;
        }
    }
}
