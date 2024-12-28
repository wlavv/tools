@extends('layouts.app')

    @include("customTools.oriflame.includes.js")
    @include("customTools.oriflame.includes.css")

@section('content')


    <div class="row">
        @include("customTools.oriflame.includes.counters", [ 'counters' => $kpi])
        <div class="col-lg-12">    
            <div class="navbar navbar-light customPanel" id="orders_list">
                <table class="table table-striped table-hover text-center">
                    <tr>
                        <td style="width: 70px;">ORDER</td>
                        <td style="width: 250px;">CUSTOMER</td>
                        <td style="width: 200px;">STATUS</td>
                        <td></td>
                        <td style="width: 200px;">TOTAL</td>
                        <td style="width: 120px;">ORDER DATE</td>
                        <td style="width: 70px;"></td>
                    </tr>
                    @if(count($orders) > 0)
                        @foreach($orders AS $order)
                        <tr class="align-middle">
                            <td>{{$order->id_order}}</td>
                            <td>{{$order->customer->firstname}} {{$order->customer->lastname}}</td>
                            <td>
                                @if( $order->current_state == 1)   
                                    <div class="alert alert-info padding-5 m-0" role="alert"> OPEN ORDER</div>
                                @elseif( $order->current_state == 2 ) 
                                    <div class="alert alert-primary padding-5 m-0" role="alert"> CLOSED ORDER</div>
                                @elseif( $order->current_state == 3 ) 
                                    <div class="alert alert-success padding-5 m-0" role="alert"> SHIPPED</div>
                                @elseif( $order->current_state == 4 ) 
                                    <div class="alert alert-danger  padding-5 m-0" role="alert"> CANCELLED</div>
                                @else
                                    <div class="alert alert-dark  padding-5 m-0" role="alert"> UNKNOWN</div>
                                @endif
                            </td>
                            <td></td>
                            <td> @include('includes.utilities.currency',   [ 'value' => $order->total ]) €</td>
                            <td> @include('includes.utilities.date',       [ 'date'  => date_create($order->created_at) ])</td>
                            <td>
                                <button class="btn btn-warning padding-10"  type="button" onclick="$('.rowsDetails').css('display', 'none'); $('#orderDetail_{{$order->id_customer}}').toggle()"> <i class="fa-solid fa-pencil"></i> </button>
                            </td>
                        </tr>
                        <tr class="rowsDetails" style="display: none;background-color: #FFF;--bs-table-bg: #FFF;" id="orderDetail_{{$order->id_customer}}">
                            <td></td>
                            <td colspan="5">
                                <div class="margin-20-0">
                                    <div class="navbar navbar-light customPanel margin-5" style="width: 29%; float: left; margin: 0;">
                                        <table>
                                            <tr>
                                                <td class="text-right padding-5 bold">NAME:</td>
                                                <td class="text-left  padding-5">{{$order->customer->firstname}} {{$order->customer->lastname}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right padding-5 bold">EMAIL:</td>
                                                <td class="text-left  padding-5">@include('includes.utilities.email',  [ 'email' => $order->customer->email ])</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right padding-5 bold">PHONE:</td>
                                                <td class="text-left  padding-5">{{$order->customer->phone}}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right padding-5 bold">CUSTOMER SINCE:</td>
                                                <td class="text-left  padding-5">@include('includes.utilities.date',   [ 'date' => date_create($order->customer->created_at) ])</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div class="navbar navbar-light customPanel margin-0-10" style="width: 40%; float: left;">
                                        <h5>CHANGE ORDER STATUS</h5>
                                        @if( $order->current_state == 4)
                                            <button class="btn btn-info padding-10 margin-10" type="button" onclick="changeOrderStatus({{$order->id_order}}, 1)">   
                                                <i class="fa-solid fa-box-open font-30"></i> 
                                                <br>
                                                <div class="margin-5"> OPEN ORDER? </div>
                                            </button>
                                        @elseif( $order->current_state == 1)
                                            <button class="btn btn-primary padding-10 margin-10" type="button" onclick="changeOrderStatus({{$order->id_order}}, 2)">   
                                                <i class="fa-solid fa-box font-30"></i> 
                                                <br>
                                                <div class="margin-5"> CLOSE ORDER? </div>
                                            </button>
                                        @elseif( $order->current_state == 3)
                                        
                                        @else
                                            <button class="btn btn-info padding-10 margin-10" type="button" onclick="changeOrderStatus({{$order->id_order}}, 1)">   
                                                <i class="fa-solid fa-box-open font-30"></i> 
                                                <br>
                                                <div class="margin-5"> OPEN ORDER? </div>
                                            </button>
                                            <button class="btn btn-success padding-10 margin-10" type="button" onclick="changeOrderStatus({{$order->id_order}}, 3)">   
                                                <i class="fa fa-truck-fast font-30"></i> 
                                                <br>
                                                <div class="margin-5"> SHIP ORDER? </div>
                                            </button>
                                        @endif
                                        @if( $order->current_state != 4 )
                                            <button class="btn btn-danger padding-10 margin-10" type="button" onclick="changeOrderStatus({{$order->id_order}}, 4)">   
                                                <i class="fa fa-xmark font-30"></i> 
                                                <br>
                                                <div class="margin-5"> CANCEL ORDER? </div>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="navbar navbar-light customPanel margin-5" style="width: 29%; float: right; margin: 0;">
                                        
                                    </div>
                                </div>
                                <table class="table table-hover text-center" style="background-color: #FFF;--bs-table-bg: #FFF;margin-top: 20px;display: inline-table;">
                                    <tr>
                                        <td><div class="desktop">IMAGE</div></td>
                                        <td>PRODUCT</td>
                                        <td><div class="desktop">QUANTITY</div><div class="mobile">QTY</div></td>
                                        <td>UNITARY</td>
                                        <td>TOTAL</td>
                                        <td></td>
                                    </tr>
                                    @foreach($order->order_details AS $detail)
                                        <tr>
                                            <td>
                                                <img class="imageHover" src="{{$detail->product->image}}" style="width: 50px;">
                                            </td>
                                            <td>
                                                <div class="desktop">{{$detail->product->name}}</div>
                                                <div style="color: #888;">{{$detail->reference}}</div>
                                            </td>
                                            <td>
                                            @if( $order->current_state != 1 )
                                                {{$detail->quantity}}
                                            @else
                                                <input name="quantity[]" type="number" id="quantity_{{$detail->reference}}" value="{{$detail->quantity}}" style="text-align: center; width: 80px;"  onblur="updateProductQuantity({{$detail->id_order}}, {{$detail->id_product}}, {{$detail->reference}})" type="number" ></td>
                                            @endif
                                            <td>{{$detail->unitary_price}} €</td>
                                            <td>{{$detail->price}} €</td>
                                            <td>        
                                                <i class="fa-solid fa-xmark" style="color: red; font-size: 22px;" onclick="removeRow({{$detail->id_order}}, {{$detail->id_product}}, '{{$detail->reference}}')"></i>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </td>
                            <td></td>
                        </tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="5">
                            <div class="alert alert-warning" role="alert" style="margin: 10px;"> NO ORDERS FOR SELECTED STATE </div>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel" id="newOrderContainer" style="display: none;">
                @include("customTools.oriflame.includes.newOrder")
            </div> 
            <div class="navbar navbar-light customPanel" id="orderContainer" style="display: none;">
                
            </div>            
        </div>
    </div>

    <style>
        .imageHover:hover{ 
            transform: scale(5);
            border: 1px solid #999;
            transition: -webkit-transform 0.3s ease-in-out;
            transition: transform 0.3s ease-in-out;
            box-shadow: 0px 0px 5px 3px rgb(50, 50, 50);
         }
    </style>
@endsection
