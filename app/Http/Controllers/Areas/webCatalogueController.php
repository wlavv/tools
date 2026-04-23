<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class webCatalogueController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addAccess( route('asset_library.index'), 'WebCatalog - ASSETS', 'fa-folder-open');
        return $this->view('areas/webCatalogue/index');
    }
    
}
