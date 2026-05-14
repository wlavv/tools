<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class purchasingController extends Controller
{
    protected bool $hasPageActions = false;

    public function index()
    {
        $this->addRouteAccess('erp.dashboard', 'ERP', 'fa-solid fa-diagram-project');
        $this->addRouteAccess('erp.timeline', 'Timeline ERP', 'fa-solid fa-timeline');
        $this->addRouteAccess('erp.settings.index', 'Configuracao ERP', 'fa-solid fa-sliders');

        return $this->view('areas/purchasing/index');
    }
}
