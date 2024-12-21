<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;

class marketingController extends Controller{
    
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'marketing', 'url' => route('marketing.index')];
    }

    public function index(){
        
        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => [],
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/marketing/index')->with($data);
    }
    
}

