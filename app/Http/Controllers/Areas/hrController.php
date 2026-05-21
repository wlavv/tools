<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class hrController extends Controller{
    
    protected bool $hasPageActions = false;

    public function index(){
        $this->addRouteAccess('document-manager.dashboard', 'Document Manager', 'fa-solid fa-folder-tree');
        $this->addRouteAccess('calendar.index', 'Calendar', 'fa-regular fa-calendar');
        return $this->view('areas/hr/index');
    }
}
