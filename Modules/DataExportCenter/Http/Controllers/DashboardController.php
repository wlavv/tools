<?php

namespace Modules\DataExportCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DataExportCenter\Services\ExportReadinessService;

class DashboardController extends Controller
{
    public function index(ExportReadinessService $readiness)
    {
        return $this->view('data-export-center::dashboard', [
            'summary' => $readiness->summary(),
        ]);
    }
}
