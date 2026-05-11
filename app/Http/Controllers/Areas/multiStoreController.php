<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class multiStoreController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addAccess( route('catalog-manager.dashboard'), 'Catalog Manager', 'fa-solid fa-boxes-stacked' );
        $this->addAccess( route('document-manager.dashboard'), 'Document Manager', 'fa-solid fa-folder-tree' );

        return $this->view('areas/multiStore/index');
    }
    
}
