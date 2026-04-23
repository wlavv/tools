<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class financeController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        return $this->view('areas/finance/index');
    }
    
}