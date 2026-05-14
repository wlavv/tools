<?php

namespace Modules\IntegrationHealth\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\IntegrationHealth\Services\IntegrationHealthService;

class IntegrationHealthDashboardController extends Controller
{
    public function index(IntegrationHealthService $healthService)
    {
        return $this->view('integration-health::dashboard.index', $healthService->dashboard());
    }
}
