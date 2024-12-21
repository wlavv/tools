@extends('layouts.app')

    @include("customTools.customers.includes.js")
    @include("customTools.customers.includes.css")

@section('content')

    <div class="row">    
        <div class="col-lg-12">        
            <div class="navbar navbar-light customPanel" style="display: flow-root;">
                <div class="listUL" style="text-transform: uppercase;font-weight: bolder;text-align: right;"> 
                    <div class="rowStyling" style="width: 110px; height: 90px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;margin: 0 10px;float: left;"> 
                        <a href="#"> 
                            <div> <i class="fa-solid fa-chart-pie" style="font-size: 25px;"></i> </div>
                            <div style="padding-top: 5px;font-size: 16px;">STATISTICS</div>
                        </a>
                    </div>
                    <div class="rowStyling" style="width: 110px; height: 90px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;margin: 0 10px;float: right;" onclick="addCustomerContainer()"> 
                        <a href="#"> 
                            <div> <i class="fa-solid fa-user-plus" style="font-size: 25px;"></i> </div>
                            <div style="padding-top: 5px;font-size: 14px;">CUSTOMER</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">    
            <div class="navbar navbar-light customPanel">
                <table class="table table-striped table-hover text-center">
                    <tr>
                        <td></td>
                        <td>NAME</td>
                        <td><span class="desktop"> STATUS </span></td>
                        <td>EMAIL</td>
                        <td>PHONE</td>
                        <td><span class="desktop"> REGISTER DATE </span></td>
                    </tr>



                    @if( count($customers) > 0)
                    @foreach($customers AS $customer)
                        <tr>
                            <td> @include('includes.utilities.remove', [ 'id' => $customer->id_customer, 'route' => route('customers.destroy', $customer->id_customer) ]) </td>
                            <td onclick="editCustomerContainer({{ $customer->id_customer }}, '{{ route('customers.edit', $customer->id_customer) }}')">
                                <span class="desktop">{{$customer->firstname}} {{$customer->lastname}}</span>
                                <span class="mobile">{{$customer->firstname[0]}}. {{$customer->lastname}}</span>
                            </td>
                            <td><span class="desktop"> @include('includes.utilities.active', [ 'active' => $customer->active, 'id' => $customer->id_customer, 'route' => route('customers.active')  ]) </span></td>
                            <td> @include('includes.utilities.email',  [ 'email' => $customer->email ])</td>
                            <td> @include('includes.utilities.phone',  [ 'id_customer' => $customer->id_customer, 'phone' => $customer->phone ])</td>
                            <td onclick="editCustomerContainer({{ $customer->id_customer }}, '{{ route('customers.edit', $customer->id_customer) }}')"><span class="desktop"> @include('includes.utilities.date',   [ 'date' => date_create($customer->created_at) ])</span></td>
                        </tr>
                        <tr id="whatsappContainer_{{$customer->id_customer}}" id="whatsappContainer" style="display: none;">
                            <td colspan="7">
                                <div class="row">
                                    <div class="col-lg-1"> </div>
                                    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6"> <button style="height: 80px; width: calc(100% - 10px); margin: 5px;" class="btn btn-warning"   type="button" onclick="sendWhatsapp('{{$customer->phone}}', '{{$customer->firstname . ' ' . $customer->lastname}}', 'backorder')">  BACKORDER</button> </div>
                                    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6"> <button style="height: 80px; width: calc(100% - 10px); margin: 5px;" class="btn btn-danger"    type="button" onclick="sendWhatsapp('{{$customer->phone}}', '{{$customer->firstname . ' ' . $customer->lastname}}', 'cancelled')">  CANCELLED</button> </div>
                                    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6"> <button style="height: 80px; width: calc(100% - 10px); margin: 5px;" class="btn btn-primary"   type="button" onclick="sendWhatsapp('{{$customer->phone}}', '{{$customer->firstname . ' ' . $customer->lastname}}', 'pickup')">     READY TO PICKUP</button> </div>
                                    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6"> <button style="height: 80px; width: calc(100% - 10px); margin: 5px;" class="btn btn-success"   type="button" onclick="sendWhatsapp('{{$customer->phone}}', '{{$customer->firstname . ' ' . $customer->lastname}}', 'delivery')">   READY TO DELIVERY</button> </div>
                                    <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6"> <button style="height: 80px; width: calc(100% - 10px); margin: 5px;" class="btn btn-dark"      type="button" onclick="sendCustomMessage('{{$customer->phone}}')">          CUSTOM MESSAGE</button> </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="7">NO CUSTOMERS REGISTERED!</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>   

        <div class="col-lg-4">      
            <div class="navbar navbar-light customPanel" id="defaultCustomersContainer" style="text-align: center;"> 
            <div class="alert alert-info" role="alert" style="margin-bottom: 0;"> 
                <i class="fa-solid fa-circle-info" style="font-size: 45px;"></i>
                <br><br>
                <div>Select a customer on the left to edit!</div>
            </div>
   
            </div>  
            <div class="navbar navbar-light customPanel" style="display: none;" id="editCustomersContainer"> </div>  
            <div class="navbar navbar-light customPanel" style="display: none;" id="addCustomersContainer">

                <form style="margin: 0;" method="post" action="{{route('customers.store')}}">
                    @csrf
                    <div class="row">
                        @include('includes.utilities.textInput', [ 'field' => 'firstname', 'name' => 'FIRSTNAME', 'placeholder' => 'FIRSTNAME', 'id' => 'firstname' ])
                        @include('includes.utilities.textInput', [ 'field' => 'lastname',  'name' => 'LASTNAME',  'placeholder' => 'LASTNAME',  'id' => 'lastname' ])
                        @include('includes.utilities.textInput', [ 'field' => 'email',     'name' => 'EMAIL',     'placeholder' => 'EMAIL ADDRESS', 'id' => 'email' ])
                        @include('includes.utilities.textInput', [ 'field' => 'phone',     'name' => 'PHONE',     'placeholder' => 'PHONE', 'id' => 'phone' ])
                        <div class="col-lg-8"> </div>
                        <div class="col-lg-4"> <button class="btn btn-success" type="submit" style="width: 100%">SAVE</button> </div>
                    </div>
                </form>
            </div>  
        </div>  
    </div>  
        
@endsection
