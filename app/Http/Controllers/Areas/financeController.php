<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


use App\Models\prestashop\orders;
use App\Models\prestashop\product;

class financeController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'finance', 'url' => route('finance.index')];
        $this->actions[]     = [];
    }

    public function index(){
        
        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => [],
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
        ];

        return View::make('areas/finance/index')->with($data);
    }

}
