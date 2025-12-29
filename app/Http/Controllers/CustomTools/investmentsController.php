<?php

namespace App\Http\Controllers\customTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;

class investmentsController extends customToolsController
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'investments', 'url' => route('investments.index')];
        $this->actions[]     = [];
    }

    public function index(){
        
        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => $this->accessList(),
            /**'actions'       => $this->actions,**/
            'breadcrumbs'   => $this->breadcrumbs,
        ];

        $this->setViewData($data);
        return View::make('areas/finance/index')->with($this->viewData);
    }

    public function accessList(){

        return [
            [
                'url' => route('broker-accounts.index'),
                'name' => "Brokers",
                'image' => null,  
                'icon' => 'fa-solid fa-building-columns',            
            ],
            [
                'url' => route('assets.index'),
                'name' => "Assets",
                'image' => null,  
                'icon' => 'fa-solid fa-glasses',            
            ],
            [
                'url' => route('positions.index'),
                'name' => "Positions",
                'image' => null,  
                'icon' => 'fa-solid fa-money-bill-trend-up',            
            ]
        ];

    }

}
