<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class hrController extends Controller{
    
    public function index(){
        $this->addAccess( route('calendar.index'), 'Calendar', 'fa-regular fa-calendar' );
        return $this->view('areas/hr/index');
    }
}