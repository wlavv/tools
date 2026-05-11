<?php

namespace Modules\DocumentManager\Http\Controllers;

use Modules\DocumentManager\Services\SearchService;

class SearchController extends BaseDocumentController
{
    public function index(SearchService $search)
    {
        return view('documentmanager::search.index', [
            'provider' => $search->provider(),
            'documents' => $search->search(request()->only(['q', 'workspace_id', 'status'])),
        ]);
    }
}
