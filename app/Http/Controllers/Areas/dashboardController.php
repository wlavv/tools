<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Modules\PermissionRoleManager\Services\RoutePermissionAccessService;

use Modules\Tasks\Services\FamilyPlannerWeatherService;
use Modules\Tasks\Services\FamilyPlannerThoughtService;

class dashboardController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){

        $this->setBreadcrumbs([]);

        $routeAccess = app(RoutePermissionAccessService::class);
        $canAccess = fn (string $routeName): bool => Route::has($routeName)
            && $routeAccess->canAccessRouteName(auth()->id(), $routeName);

        $heroActions = collect([
            ['label' => 'Administration', 'icon' => 'fa-solid fa-screwdriver-wrench', 'route' => 'administration.index'],
            ['label' => 'Webmaster', 'icon' => 'fa-solid fa-code', 'route' => 'web.index'],
            ['label' => 'Sales', 'icon' => 'fa-solid fa-basket-shopping', 'route' => 'sales.index'],
            ['label' => 'Finance', 'icon' => 'fa-solid fa-chart-line', 'route' => 'finance.index'],
            ['label' => 'Marketing', 'icon' => 'fa-solid fa-bullhorn', 'route' => 'marketing.index'],
            ['label' => 'Customer Support', 'icon' => 'fa-solid fa-headset', 'route' => 'customerSupport.index'],
            ['label' => 'HR', 'icon' => 'fa-solid fa-user-group', 'route' => 'hr.index'],
            ['label' => 'Purchasing', 'icon' => 'fa-solid fa-cart-flatbed', 'route' => 'purchasing.index'],
            ['label' => 'Logistics', 'icon' => 'fa-solid fa-truck-fast', 'route' => 'logistics.index'],
            ['label' => 'Family', 'icon' => 'fa-solid fa-hands-holding-child', 'route' => 'family.index'],
            ['label' => 'LSG', 'icon' => 'fa-solid fa-building', 'route' => 'lsg.index'],
        ])
            ->filter(fn (array $action) => $canAccess($action['route']))
            ->map(fn (array $action) => array_merge($action, ['url' => route($action['route'])]))
            ->values()
            ->all();

        $modulesCount = count(glob(base_path('Modules/*/module.json')) ?: []);

        $heroStats = [
            ['label' => 'Areas', 'value' => count($heroActions), 'icon' => 'fa-solid fa-grip'],
            ['label' => 'Modules', 'value' => $modulesCount, 'icon' => 'fa-solid fa-cubes'],
            ['label' => 'Shortcuts', 'value' => count($this->accessList), 'icon' => 'fa-solid fa-bolt'],
        ];

        $weather = app(FamilyPlannerWeatherService::class)->today();
        $dailyQuote = app(FamilyPlannerThoughtService::class)->today();

        return $this->view('areas.dashboard.index', [
            'weather' => $weather,
            'dailyQuote' => $dailyQuote,
            'heroStats' => $heroStats,
            'heroActions' => $heroActions,
        ]);
    }
    
}
