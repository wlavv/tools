<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class familyController extends Controller{

    public function index(){

        $this->setIndexPage('family', 'family.index');
        $this->addAccess( route('tasks.index'), 'TAREFAS', 'fa-solid fa-list-check' );

        return $this->view('areas/family/index');
    }
    
}
