<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class multiStoreController extends Controller{

    public function index(){

        $this->setIndexPage('multiStore', 'multiStore.index');
        return $this->view('areas/multiStore/index');
    }
    
}
