<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;

use App\Models\prestashop\product;

class adminController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->breadcrumbs[] = [ 'name' =>  'administration', 'url' => route('administration.index')];
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
                'url' => route('groupStructure.index'),
                'name' => 'Projects',
                'image' => null,            
                'icon' => 'fa-solid fa-folder-tree'            
            ],
            [
                'url' => route('budget.index'),
                'name' => 'Budget',
                'image' => null,            
                'icon' => 'fa-solid fa-euro-sign'            
            ],
        ];
    }
    
}
