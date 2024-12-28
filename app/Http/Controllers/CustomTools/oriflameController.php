<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\webToolsManager\wt_customer;
use App\Models\webToolsManager\wt_orders;
use App\Models\webToolsManager\wt_product;
use App\Models\webToolsManager\wt_orders_details;

use Symfony\Component\DomCrawler\Crawler;

class oriflameController extends customToolsController
{

    public function index(){

        $order = new wt_orders();
        $data = [
            'orders' => wt_orders::getOrders(),
            'kpi'    => wt_orders::getCounters()
        ];
        
        $this->setViewData($data);
        return View::make('customTools/oriflame/index')->with( $this->viewData );
    }

    public function list($current_state){

        $data = [
            'orders' => wt_orders::getOrders($current_state),
            'kpi'    => wt_orders::getCounters(),
        ];

        $this->setViewData($data);
        return View::make('customTools/oriflame/index')->with( $this->viewData );
    }

    public function getOrderProducts(Request $request){

        $order = wt_orders::with('order_details.product')->where('id_customer', $request->id_customer)->where('current_state', 1)->first();
        return view('customTools/oriflame/includes/product/order', compact('order'))->render();
    }

    public function getProductInfo(Request $request){

        $html = file_get_contents( 'https://pt.oriflame.com/products/product?code=' . $request->reference );

        $crawler = new Crawler($html);
        $title = $crawler->filter('meta[name="searchTitle"]')->first();
        $title = $title ? $title->attr('content') : 0;

        $thumb = $crawler->filter('meta[name="thumb"]')->first();
        $thumb = $thumb ? $thumb->attr('content') : 0;

        $price = $crawler->filter('[data-testid="Presentation-product-detail-prices-current-price"]')->text();

        $price = str_replace(' €', '', $price);
        $price = (float)str_replace(',', '.', $price);

        $product = [];

        if($title != 0){
            $product = [
                'reference' => $request->reference,
                'title' => $title,
                'thumb' => $thumb,
                'price' => $price,
                'id_customer' => $request->id_customer,
                'quantity' => 1
            ];

            $product['id_product'] = wt_product::addProduct($product);

            $product['id_order'] = wt_orders::addProduct($product);
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

    public function updateProductQuantity(Request $request){
        return wt_orders_details::updateQuantity($request->id_order, $request->id_product, $request->quantity );
    }

    public function removeProduct(Request $request){
        return wt_orders_details::removeProduct($request->id_order, $request->id_product );
    }

    public function changeOrderStatus(Request $request){
        return wt_orders::changeOrderStatus($request->id_order, $request->id_status );
    }

    
}
