<?php

namespace Modules\DocumentManager\Http\Controllers;

use Modules\DocumentManager\Services\DiagnosticsService;

class DiagnosticsController extends BaseDocumentController
{
    public function index(DiagnosticsService $diagnostics)
    {
        return view('documentmanager::diagnostics.index', [
            'report' => $diagnostics->report(),
        ]);
    }
}
