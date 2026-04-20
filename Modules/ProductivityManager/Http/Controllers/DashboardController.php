<?php

namespace Modules\ProductivityManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\ProductivityManager\Services\ProductivityDashboardService;

class DashboardController extends Controller
{
    public function __construct(protected ProductivityDashboardService $dashboardService)
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('productivitymanager::dashboard.index', [
            'dashboard' => $this->dashboardService->getDashboardData(),
        ]);
    }

    public function dashboard(): View
    {
        return $this->index();
    }
}
