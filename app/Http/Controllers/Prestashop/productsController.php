<?php

namespace App\Http\Controllers\Prestashop;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use App\Models\prestashop\product;
use App\Models\prestashop\product_shop;
use App\Models\prestashop\currency_convertion;

class productsController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct(){
        $this->breadcrumbs[] = [ 'name' =>  'products', 'url' => route('products.index')];
    }

    public function index(){
        $data = [
            'actions'    => $this->actions,
            'breadcrumbs'=> $this->breadcrumbs,
            'products'   => product::take(50)->get()
        ];

        return View::make('prestashop/products/index')->with($data);
    }

}