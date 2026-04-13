<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class financeController extends Controller{

    public function index(){

        $this->setIndexPage('finance', 'finance.index');
        $this->addAccess( route('budget.index'),        'Budget',       'fa-solid fa-euro-sign' );
        $this->addAccess( route('investments.index'),   "Investments",  'fa-solid fa-money-bill-trend-up',  );
    
        return $this->view('areas/finance/index');
    }
    
}