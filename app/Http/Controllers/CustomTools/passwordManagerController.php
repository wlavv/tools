<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\webToolsManager\wt_password_manager;

class passwordManagerController extends customToolsController
{

    public function index(){

        $data = [
            'credentials' => wt_password_manager::get()
        ];
        
        $this->setViewData($data);
        return View::make('customTools/passwordManager/index')->with( $this->viewData );
    }

    public function actions(Request $request){

        if($request->action == 'add') wt_password_manager::addData($request);
        if($request->action == 'update') wt_password_manager::updateData($request);
        if($request->action == 'destroy') wt_password_manager::destroyData($request);
    }
}
