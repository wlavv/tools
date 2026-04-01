<?php

namespace Modules\ProductivityManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Modules\ProductivityManager\Services\ProductivityDashboardService;

class DashboardController extends Controller
{
    public array $breadcrumbs = [];
    public array $actions = [];

    public function __construct(
        protected ProductivityDashboardService $dashboardService
    ) {
        $this->middleware('auth');
        $this->breadcrumbs[] = ['name' => 'administration', 'url' => route('administration.index')];
        $this->breadcrumbs[] = ['name' => 'productivity manager', 'url' => route('productivityManager.index')];

        $this->actions = [
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
    }

    public function index(): View
    {
        return view('productivitymanager::dashboard.index', [
            'actions' => $this->actions,
            'breadcrumbs' => $this->breadcrumbs,
            'dashboard' => $this->dashboardService->getDashboardData(),
        ]);
    }

    public function dashboard(): View
    {
        return $this->index();
    }
}
