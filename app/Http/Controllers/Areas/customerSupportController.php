<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class customerSupportController extends Controller{

    public function index(){
        $this->setIndexPage('customer support', 'customerSupport.index');

        return $this->view('areas/customers/index');
    }
    
}