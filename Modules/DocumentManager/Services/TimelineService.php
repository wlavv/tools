<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Modules\DocumentManager\Support\DocumentTable;

class TimelineService
{
    public function forDocument(int $documentId)
    {
        if (!DocumentTable::exists('document_logs_activity')) {
            return collect();
        }

        return DB::table('document_logs_activity')
            ->where('document_id', $documentId)
            ->orderByDesc('created_at')
            ->limit(80)
            ->get();
    }
}
