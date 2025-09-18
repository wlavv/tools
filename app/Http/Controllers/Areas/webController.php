<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;

class webController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'webmaster', 'url' => route('web.index')];
    }

    public function index()
    {
        $data = [
            'actions'    => $this->actions,
            'counters'   => [],
            'breadcrumbs'=> $this->breadcrumbs,
            'accessList' => $this->accessList()
        ];

        return View::make('areas/administration/index')->with($data);
    }


    public function accessList(){

        return [
            [
                'url' => route('mtg.index'),
                'name' => 'MTG',
                'image' => '<img src="/images/mtg/mana/mtg.png" style="width: 70px;">',            
                'icon' => null            
            ]
        ];
    }
    
}
