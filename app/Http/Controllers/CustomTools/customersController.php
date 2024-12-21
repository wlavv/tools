<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\prestashop\customer;

class customersController extends Controller
{
    public $actions;
    public $breadcrumbs;
    
    public function __construct()
    {
        $this->breadcrumbs[] = [ 'name' =>  'sales', 'url' => route('sales.index')];
        $this->breadcrumbs[] = [ 'name' =>  'customers', 'url' => route('customers.index')];
    }

    public function index(){

        $data = [
            'actions'       => $this->actions,
            'breadcrumbs'   => $this->breadcrumbs,
            'panels'        => $this->panels(),
            'customers'     => customer::get()
        ];
        
        return View::make('customTools/customers/index')->with($data);

    }

    private function panels(){

        return [];

    }

    public function store(Request $request){ 
        customer::addNew($request);
        return back();
    }
    
    public function edit(string $id_customer){
        $customer = customer::getToEdit( $id_customer );
        return view('customTools/customers/includes/edit', compact('customer'));
    }

    public function update(Request $request, string $id){ 
        customer::updateRegiser($request);
        return back();
    }

    public function destroy(string $id){ 
        customer::destroy($id);
    }
    
    public function active(Request $request){ 
        customer::active($request->id, $request->active);
    }
    
}
