<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class marketingController extends Controller{

    public function index(){
        return $this->view('areas/marketing/index');
    }
    
}
