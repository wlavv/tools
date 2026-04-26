<?php

namespace App\Http\Controllers\Areas;

use App\Http\Controllers\Controller;

class settingsController extends Controller{

    protected bool $hasPageActions = false;

    public function index(){
        $this->addAccess( route('system-tools.index'), 'System maintenance', 'fa-file-code');
        $this->addAccess( route('system_logs.index'), 'System logs', 'fa-file-lines');
        return $this->view('areas/settings/index');
    }
    
}