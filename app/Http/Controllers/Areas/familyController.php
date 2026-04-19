<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class familyController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addAccess( route('tasks.index'), 'TAREFAS', 'fa-solid fa-list-check' );
        return $this->view('areas/family/index');
    }
    
}
