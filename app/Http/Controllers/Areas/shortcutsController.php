<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class shortcutsController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addAccess( route('mtg.index'),           'MTG',                  null,                   '<img src="/images/mtg/mana/mtg.png" style="width: 70px;">');
        return $this->view('areas/shortcuts/index');
    }
    
}
