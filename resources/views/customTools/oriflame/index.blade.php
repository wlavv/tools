@extends('layouts.app')

    @include("customTools.oriflame.includes.js")
    @include("customTools.oriflame.includes.css")

@section('content')


    <div class="row">
        @include("customTools.oriflame.includes.counters", [ 'counters' => $kpi])
        <div class="col-lg-12">    
            <div class="navbar navbar-light customPanel">
                <table class="table table-striped table-hover text-center">
                    <tr>
                        <td>CUSTOMER</td>
                        <td>EMAIL</td>
                        <td>STATUS</td>
                        <td>TOTAL</td>
                        <td>ORDER DATE</td>
                    </tr>
                    @if(count($orders) > 0)
                    @foreach($orders AS $order)
                    <tr>
                        <td>{{$order->id_customer}}</td>
                        <td>EMAIL</td>
                        <td>{{$order->current_sate}}</td>
                        <td>{{$order->total}} €</td>
                        <td> @include('includes.utilities.date',   [ 'date' => date_create($order->created_at) ])</td>
                    </tr>
                    <tr style="display: none" id="orderDetail_{{$order->id_customer}}">
                        <td>{{$order->id_customer}}</td>
                        <td>EMAIL</td>
                        <td>{{$order->current_sate}}</td>
                        <td>{{$order->total}} €</td>
                        <td> @include('includes.utilities.date',   [ 'date' => date_create($order->created_at) ])</td>
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
            transform: scale(10);
            border: 1px solid #999;
            transition: -webkit-transform 0.3s ease-in-out;
            transition: transform 0.3s ease-in-out;
            box-shadow: 0px 0px 5px 3px rgb(50, 50, 50);
         }
    </style>
@endsection
