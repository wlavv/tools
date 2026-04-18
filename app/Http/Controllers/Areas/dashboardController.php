<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

use Modules\Tasks\Services\FamilyPlannerWeatherService;
use Modules\Tasks\Services\FamilyPlannerThoughtService;

class dashboardController extends Controller{

    public function index(){

        $this->setBreadcrumbs([]);


        $heroStats = [
            ['label' => 'Areas', 'value' => 9, 'icon' => 'fa-solid fa-grip'],
            ['label' => 'Modules', 'value' => 12, 'icon' => 'fa-solid fa-cubes'],
            ['label' => 'Shortcuts', 'value' => count($this->accessList), 'icon' => 'fa-solid fa-bolt'],
        ];

        $heroActions = [
            ['label' => 'Administration', 'icon' => 'fa-solid fa-screwdriver-wrench', 'url' => route('administration.index')],
            ['label' => 'Web', 'icon' => 'fa-solid fa-globe', 'url' => route('web.index')],
            ['label' => 'Sales', 'icon' => 'fa-solid fa-basket-shopping', 'url' => route('sales.index')],
            ['label' => 'Finance', 'icon' => 'fa-solid fa-chart-line', 'url' => route('finance.index')],
            ['label' => 'Marketing', 'icon' => 'fa-solid fa-bullhorn', 'url' => route('marketing.index')],
            ['label' => 'Customer Support', 'icon' => 'fa-solid fa-headset', 'url' => route('customerSupport.index')],
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