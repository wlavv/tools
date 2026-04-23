<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class webController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        return $this->view('areas/web/index');
    }
    
}
