<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;

use App\Models\webToolsManager\wt_projects;

class projectDetailsController extends customToolsController
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id)
    {
        $data = [
            'actions'    => $this->actions,
            'counters'   => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => [],
            'projects' => wt_projects::getProjects()
        ];

        $this->setViewData($data);
        
        return View::make('customTools/projects/details')->with($this->viewData);
    }

}
