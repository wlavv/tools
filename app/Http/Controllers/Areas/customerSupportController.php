<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;
use App\Models\prestashop\orders;

class customerSupportController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'customer support', 'url' => route('customerSupport.index')];

    }

    public function index()
    {
        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => [],
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];
 
        return View::make('areas/customer/index')->with($data);
    }

}
