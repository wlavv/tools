<?php

namespace Modules\ProductivityManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\ProductivityManager\Services\ProductivityDashboardService;

class DashboardController extends Controller
{
    public array $breadcrumbs = [];

    public function __construct( protected ProductivityDashboardService $dashboardService) {
    
        $this->middleware('auth');
        $this->setIndexPage('productivity manager', 'productivityManager.index');
    }

    public function index(): View{

        $actions = [
            [
                'url' => route('productivityManager.dashboard'),
                'name' => 'Dashboard',
                'icon' => 'fa-solid fa-gauge-high',
                'class' => 'outline-primary',
            ],
            [
                'url' => route('productivityManager.settings'),
                'name' => 'Settings',
                'icon' => 'fa-solid fa-cog',
                'class' => 'outline-primary',
            ],
        ];

        $data = [
            'actions' => $actions,
            'breadcrumbs' => $this->breadcrumbs,
            'dashboard' => $this->dashboardService->getDashboardData(),
        ];
        return $this->view('productivitymanager::dashboard.index', $data );
    }

    public function dashboard(): View{
        return $this->index();
    }
}
