<?php

namespace Modules\DocumentManager\Http\Controllers;

use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Services\OcrService;
use Modules\DocumentManager\Support\DocumentTable;

class DocumentOcrController extends BaseDocumentController
{
    public function show(int $document)
    {
        $documentRecord = Document::query()->findOrFail($document);

        return view('documentmanager::documents.ocr', [
            'document' => $documentRecord,
            'ocrResults' => DocumentTable::safeGet('document_ai_ocr', function ($query) use ($document) {
                $query->where('document_id', $document)->orderByDesc('id');
            }),
        ]);
    }

    public function process(int $document, OcrService $ocr)
    {
        $documentRecord = Document::query()->findOrFail($document);
        $versionId = $documentRecord->current_version_id;
        $result = $ocr->process($documentRecord->id, $versionId);

        if (($result['status'] ?? null) === 'failed') {
            return back()->with('error', $result['message'] ?? 'OCR failed.');
        }

        return redirect()
            ->route('document-manager.documents.ocr.show', $documentRecord->id)
            ->with('success', 'OCR processado.');
    }
}
