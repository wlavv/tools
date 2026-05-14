<?php

namespace Modules\DataImportWizard\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\DataImportWizard\Models\DataImportBatch;
use Modules\DataImportWizard\Services\ImportReadinessService;

class DashboardController extends Controller
{
    public function index(ImportReadinessService $readiness)
    {
        return $this->view('data-import-wizard::dashboard', [
            'readiness' => $readiness->summary(),
            'recentBatches' => DataImportBatch::query()->latest()->limit(10)->get(),
        ]);
    }
}
