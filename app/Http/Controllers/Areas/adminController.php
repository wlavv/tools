<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class adminController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){

        $this->addAccess( route('project_manager.index'),       'Projects',         'fa-solid fa-folder-tree' );
        $this->addAccess( route('password_manager.index'),      "PASSWORD'S",       'fa-solid fa-key' );           
        $this->addAccess( route('ai_consensus.index'),          "Consensus",        'fa-solid fa-star-of-life' );
        $this->addAccess( route('productivity_manager.index'),  "Productivity",     'fa-solid fa-bridge-circle-check' );
        $this->addAccess( route('roadmap.dashboard'),           "Roadmap",          'fa-solid fa-timeline' );

        return $this->view('areas/administration/index');
    }
}
