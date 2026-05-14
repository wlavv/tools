<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class adminController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){

        $this->addRouteAccess('project_manager.index', 'Projects', 'fa-solid fa-folder-tree');
        $this->addRouteAccess('document-manager.dashboard', 'Document Manager', 'fa-solid fa-folder-tree');
        $this->addRouteAccess('password_manager.index', "PASSWORD'S", 'fa-solid fa-key');
        $this->addRouteAccess('ai_consensus.index', 'Consensus', 'fa-solid fa-star-of-life');

        return $this->view('areas/administration/index');
    }
}
