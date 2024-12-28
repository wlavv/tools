<?php

namespace App\Http\Controllers\CustomTools;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

use App\Models\webToolsManager\wt_customer;

class customersController extends customToolsController{
    
    public function index(){
        $this->setViewData( [ 'customers' => wt_customer::get() ] );
        return View::make('customTools/customers/index')->with( $this->viewData );
    }

    public function store(Request $request){ 
        wt_customer::addNew($request);
        return back();
    }
    
    public function edit(string $id_customer){
        $customer = wt_customer::getToEdit( $id_customer );
        $customer['update'] = route('customers.update', $customer->id_customer);
        return $customer;
    }

    public function update(Request $request, string $id){ 
        wt_customer::updateRegister($request, $id);
        return back();
    }

    public function destroy(string $id){ 
        wt_customer::destroy($id);
    }
    
    public function active(Request $request){ 
        wt_customer::active($request->id, $request->active);
    } 

    public function getCustomerInfo(Request $request){

        $customer = wt_customer::getCustomer($request->customer);

        if(count($customer) == 1){
            return [
                'type' => 'details',
                'id_customer' => $customer[0]->id_customer,
                'email' => $customer[0]->email,
                'html' => view('customTools/customers/customerDetails', compact('customer'))->render()
            ];
        }

        if(count($customer) >  1){
            return [
                'type' => 'list',
                'html' => view('customTools/customers/customersList', compact('customer'))->render()
            ];
        }

        return [
            'type' => 'none',
            'html' => view('customTools/customers/noCustomer')->render()
        ];
    } 
}