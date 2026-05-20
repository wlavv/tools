<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class hrController extends Controller{
    
    protected bool $hasPageActions = false;

    public function index(){
        $this->addRouteAccess('calendar.index', 'Calendar', 'fa-regular fa-calendar');
        $this->addRouteAccess('tasks.index', 'Tasks', 'fa-solid fa-list-check');
        return $this->view('areas/hr/index');
    }
}
