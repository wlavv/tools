<?php

namespace App\Http\Controllers\CustomTools;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\webToolsManager\wt_customer;
use App\Models\webToolsManager\wt_orders;

use Symfony\Component\DomCrawler\Crawler;

class ordersController extends customToolsController
{

    public function index(){

        $data = [
            'orders' => wt_orders::customer()->order_details()->getOrders(),
            'kpi'    => wt_orders::getCounters()
        ];
        
        $this->setViewData($data);
        return View::make('customTools/oriflame/index')->with( $this->viewData );
    }

    public function list($current_state){

        $data = [
            'orders' => wt_orders::customer()->order_details()->getOrders($current_state),
            'kpi'    => wt_orders::getCounters(),
        ];

        $this->setViewData($data);
        return View::make('customTools/oriflame/index')->with( $this->viewData );
    }

    public function displayOrder(){
        return view('customTools/oriflame/includes/displayOrder');
    }
}
