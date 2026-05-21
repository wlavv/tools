<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class webController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addRouteAccess('ai_consensus.index', 'AI Consensus', 'fa-solid fa-brain');
        $this->addRouteAccess('module_compliance_center.dashboard', 'Compliance Center', 'fa-solid fa-shield-halved');

        return $this->view('areas/web/index');
    }
    
}
