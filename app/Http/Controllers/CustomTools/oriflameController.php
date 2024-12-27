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
                'id_customer' => $request->id_customer
            ];

            $product['id_product'] = wt_product::addProduct($product);

            wt_orders::addProduct($product);
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
