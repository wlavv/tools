<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class settingsController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        return $this->view('areas/settings/index');
    }
    
}