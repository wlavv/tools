<?php

namespace App\Http\Controllers\Areas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class dashboardController extends Controller
{
    public $actions;
    public $breadcrumbs;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){

        $this->breadcrumbs[] = [ 'name' =>  trans('breadcrumbs.dashboard'), 'url' => route('dashboard.index')];

        $data = [
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'counters'      => [],
            'panels'        => [],
        ];

        return View::make('areas/dashboard/index')->with($data);
    }

    public function post(Request $request){

    }


}
