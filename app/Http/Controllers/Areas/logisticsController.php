<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\orders;
use App\Models\prestashop\product;

class logisticsController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'logistics', 'url' => route('logistics.index')];
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

        return View::make('areas/logistics/index')->with($data);
    }

}
