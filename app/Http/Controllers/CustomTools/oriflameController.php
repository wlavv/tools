<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\customer;
use App\Models\prestashop\orders;

use Symfony\Component\DomCrawler\Crawler;

class oriflameController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  'sales', 'url' => route('sales.index')];
        $this->breadcrumbs[] = [ 'name' =>  'oriflame', 'url' => route('oriflame.index')];
    }

    public function index(){

        $data = [
            'orders'        => orders::getOrders(),
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'panels'        => $this->panels()
        ];
        
        return View::make('customTools/oriflame/index')->with($data);

    }

    private function panels(){

        return [
            'openOrder' => ''
        ];

    }


    public function create(){ }
    public function store(Request $request){ }
    public function show(string $id){ }
    public function edit(string $id){ }
    public function update(Request $request, string $id){ }
    public function destroy(string $id){ }

    
    public function displayOrder(){
        return view('customTools/oriflame/includes/displayOrder');
    }

    public function getCustomerInfo(Request $request){

        $customer = customer::getCustomer($request->customer);

        if(count($customer) == 1){
            return [
                'type' => 'details',
                'html' => view('customTools/oriflame/includes/customer/customerDetails', compact('customer'))->render()
            ];
        }

        if(count($customer) >  1){
            return [
                'type' => 'list',
                'html' => view('customTools/oriflame/includes/customer/customersList', compact('customer'))->render()
            ];
        }

        return [
            'type' => 'none',
            'html' => view('customTools/oriflame/includes/customer/noCustomer')->render()
        ];
    }

    public function getProductInfo(Request $request){

        $html = file_get_contents( 'https://pt.oriflame.com/products/product?code=' . $request->reference );

        $crawler = new Crawler($html);
        $title = $crawler->filter('meta[name="searchTitle"]')->first();
        $title = $title ? $title->attr('content') : 0;

        $thumb = $crawler->filter('meta[name="thumb"]')->first();
        $thumb = $thumb ? $thumb->attr('content') : 0;

        $price = $crawler->filter('[data-testid="Presentation-product-detail-prices-current-price"]')->text();

        $product = [];

        if($title != 0){
            $product = [
                'reference' => $request->reference,
                'title' => $title,
                'thumb' => $thumb,
                'price' => $price,
                'id_customer' => $request->id_customer
            ];

            orders::addProduct($product);
        }

        if(count($product) > 0){
            return [
                'type' => 'details',
                'html' => view('customTools/oriflame/includes/product/productRow', compact('product'))->render()
            ];
        }

        return [
            'type' => 'none',
            'html' => view('customTools/oriflame/includes/product/noProduct')->render()
        ];
    }
    
}
