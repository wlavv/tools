<?php

namespace Modules\DocumentManager\Http\Controllers;

use Modules\DocumentManager\Services\SearchService;

class SearchController extends BaseDocumentController
{
    public function index(SearchService $search)
    {
        $documents = $search->search(request()->only(['workspace_id', 'status']));
        $requestedPreviewId = request('preview_id');
        $selectedDocument = collect($documents->items())
            ->firstWhere('id', is_numeric($requestedPreviewId) ? (int) $requestedPreviewId : $requestedPreviewId)
            ?? collect($documents->items())->first();

        return view('documentmanager::search.index', [
            'provider' => $search->provider(),
            'documents' => $documents,
            'selectedDocument' => $selectedDocument,
        ]);
    }
}
