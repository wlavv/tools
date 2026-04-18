<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class adminController extends Controller{

    public function index(){

        $this->addAccess( route('project_manager.index'),       'Projects',         'fa-solid fa-folder-tree' );
        $this->addAccess( route('password_manager.index'),      "PASSWORD'S",       'fa-solid fa-key' );           
        $this->addAccess( route('ai_consensus.index'),          "Consensus",        'fa-solid fa-star-of-life' );
        $this->addAccess( route('productivityManager.index'),   "Productivity",     'fa-solid fa-star-of-life' );
        $this->addAccess( route('roadmap.dashboard'),           "Roadmap - LSG",    'fa-solid fa-star-of-life' );

        return $this->view('areas/administration/index');
    }
    
}
