<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class dashboardController extends Controller{

    public function index(){
        $this->setIndexPage('dashboard', 'dashboard.index');
        return $this->view('areas/dashboard/index');
    }
    
}