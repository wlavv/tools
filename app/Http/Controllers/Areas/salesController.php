<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;
use App\Models\prestashop\orders;
use App\Models\prestashop\orders_details;


class salesController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'sales', 'url' => route('sales.index')];

    }

    public function index()
    {
        $data = [
            'counters'      => [],
            'panels'        => [],
            'accessList'    => $this->accessList(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs
        ];

        return View::make('areas/sales/index')->with($data);
    }

    public function accessList(){

        return [
            [
                'url' => route('oriflame.index'),
                'name' => 'Oriflame',
                'image' => '<img src="/admin/images/oriflame.png" style="width: 80px;margin-bottom: 10px;">',            
                'icon' => null            
            ],
            [
                'url' => route('customers.index'),
                'name' => 'Customers',
                'image' => null,            
                'icon' => 'fa-user'            
            ]
        ];
    }
}
