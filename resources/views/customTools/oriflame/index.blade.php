@extends('layouts.app')

    @include("customTools.oriflame.includes.js")
    @include("customTools.oriflame.includes.css")

@section('content')


    <div class="row">
        <div class="col-lg-6">
            <div class="row">
                <div class="col-md-3 col-sm-3">
                    <div class="navbar navbar-light customPanel">
                        <div class="card-counter primary">
                            <i class="fa fa-code-fork"></i>
                            <div class="count-numbers">1000</div>
                            <div class="count-name">Orders</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-3">
                    <div class="navbar navbar-light customPanel">
                        <div class="card-counter info">
                            <i class="fa fa-boxes-packing"></i>
                            <span class="count-numbers">12</span>
                            <span class="count-name">Open</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-3">
                    <div class="navbar navbar-light customPanel">
                        <div class="card-counter success">
                            <i class="fa fa-truck-fast"></i>
                            <span class="count-numbers">2312</span>
                            <span class="count-name">Shipped</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 col-sm-3">
                    <div class="navbar navbar-light customPanel">
                        <div class="card-counter danger">
                            <i class="fa fa-xmark"></i>
                            <span class="count-numbers">104</span>
                            <span class="count-name">Cancelled</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="navbar navbar-light customPanel">
                <div class="row">                
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4"></div>
                    <div class="col-lg-4">
                        <button class="btn btn-success float-end" type="button" style="padding: 12px" onclick="newOrder()">
                            <i class="fa-solid fa-cart-plus" style="font-size: 30px;"></i>
                            <br>
                            <div style="margin-top: 10px;">NEW ORDER</div>
                        </button>                        
                    </div>
                </div>
            </div>           
        </div>
        <div class="col-lg-6">    
            <div class="navbar navbar-light customPanel">
                <table class="table table-striped table-hover text-center">
                    <tr>
                        <td>CUSTOMER</td>
                        <td>EMAIL</td>
                        <td>STATUS</td>
                        <td>TOTAL</td>
                        <td>ORDER DATE</td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="col-lg-6">
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
