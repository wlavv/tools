<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class webCatalogueController extends Controller{

    public function index(){
        return $this->view('areas/webCatalogue/index');
    }
    
}
