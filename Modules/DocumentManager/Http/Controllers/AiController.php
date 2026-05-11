<?php

namespace Modules\DocumentManager\Http\Controllers;

use Modules\DocumentManager\Services\AiService;
use Modules\DocumentManager\Services\OcrService;
use Modules\DocumentManager\Support\DocumentTable;

class AiController extends BaseDocumentController
{
    public function index(AiService $ai, OcrService $ocr)
    {
        return view('documentmanager::ai.index', [
            'aiHealth' => $ai->health(),
            'ocrHealth' => $ocr->health(),
            'stats' => [
                'ocr_pending' => DocumentTable::count('document_ai_ocr', function ($query) {
                    $query->whereIn('status', ['pending', 'queued']);
                }),
                'embeddings' => DocumentTable::count('document_ai_embeddings'),
                'summaries' => DocumentTable::count('document_ai_summaries'),
                'analysis' => DocumentTable::count('document_ai_analysis'),
                'ai_logs' => DocumentTable::count('document_logs_ai'),
            ],
            'logs' => DocumentTable::safeGet('document_logs_ai', function ($query) {
                $query->orderByDesc('id')->limit(40);
            }),
        ]);
    }
}
