<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class salesController extends Controller{

    public function index(){
        $this->addAccess( route('customers.index'), 'Customers', 'fa-user' );
        $this->addAccess( route('oriflame.index'), 'Oriflame', null, '<img src="/admin/images/oriflame.png" style="width: 80px;margin-bottom: 10px;">' );
        return $this->view('areas/sales/index');
    }
    
}