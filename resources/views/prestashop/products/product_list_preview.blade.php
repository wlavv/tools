<div id="product_container_{{$product->id_product}}" style="border-radius: 10px;">
    <div style="display: flex;">
        <div style="float: left;width: 35%;background-color: #FFF;border-radius: 10px;border: 1px solid #ccc; margin: 5px 10px;">
            @if(isset($product->manufacturer))
                @if(file_exists( public_path() . '/uploads/manufacturer/ASD/' . $product->manufacturer->name . '/thumb/' . $product->reference . '.jpg'))
                    <img src="https://webtools.euromuscleparts.com/uploads/manufacturer/ASD/{{$product->manufacturer->name}}/600/{{$product->reference}}.jpg" style="border-radius: 10px;">
                @else
                    <img src="https://asd.euromuscleparts.com/img/asd/600px/{{$product->id_manufacturer}}.webp" onclick="$('#product_{{$product->id_product}}').toggle()" style="border-radius: 10px">
                @endif
            @else
            @endif
        </div>
        <div style="float: left; width: 65%; margin: 5px 10px;">
            <div style="padding: 10px; border: 1px solid #ccc; background-color: #eee;border-radius: 5px;">
                <div class="card">
                    <div class="card-header" style="text-decoration: none;text-align:left;">INFORMATION</div>
                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body" style="text-align: left;">
                            <div> <label>NAME: </label> <span>{{$product->lang->name}}</span> </div>
                            <div> <label>REFERENCE: </label> <span>{{$product->reference}}</span> </div>
                            <div> <label>HOUSING: </label> <span>{{$product->housing}}</span> </div>
                            <div> <label>EAN-13: </label> <span>{{$product->ean13}}</span> </div>
                            <div> <label>MANUFACTURER: </label> <span>{{$product->manufacturer->name}}</span> </div>
                            <div> <label>SUPPLIER: </label> <span>{{$product->supplier->name}}</span> </div>
                            <div> <label>Product exists on: </label> </div>
                            <div> 
                                <table class="table table-striped text-center table-hover" style="width: 100%;">
                                    <tr>
                                        <td></td>
                                        <td>Euro Muscle Parts</td>
                                        <td>All Stars Motorsport</td>
                                        <td>All Stars Distribution</td>
                                        <td>Euro Rider</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;">STORES</td>
                                        <td>@if($em > 0)   <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        <td>@if($asm > 0)  <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        <td>@if($asd > 0)  <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        <td>@if($er > 0)   <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;">CURRENCY CONVERTER</td>
                                        <td>@if($cc_em > 0)   <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        <td>@if($cc_asm > 0)  <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        <td>@if($cc_asd > 0)  <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                        <td>@if($cc_er > 0)   <i class="fa-solid fa-check" style="color: darkgreen;"></i> @else <i class="fa-solid fa-xmark" style="color: red;"></i> @endif</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" style="margin-top: 10px;">
                    <div class="card-header" style="text-decoration: none;text-align:left;">PRICING</div>
                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body" style="text-align: left;">
                            <table class="table table-striped text-center table-hover" style="width: 100%;">
                                <tr>
                                    <td colspan="2">PURCHASE</td>
                                    <td colspan="2">PRICE ( exVAT)</td>
                                    <td>PRICE ( wVAT)</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>BRAND</td>
                                    <td>PRODUCT</td>
                                    <td>BRAND </td>
                                    <td>PRODUCT</td>
                                    <td>CUSTOMER ( 23% )</td>
                                    <td></td>
                                </tr>
                                @if(is_null($base_price))
                                    <tr>
                                        <td>0</td>
                                        <td>{{number_format($product->wholesale_price, 2, '.', ' ')}} €</td>
                                        <td>0</td>
                                        <td>{{number_format($product->price, 2, '.', ' ')}} €</td>
                                        <td>{{number_format($product->price * 1.23, 2, '.', ' ')}} € </td>
                                        <td onclick="addProductToCurrencyConverter({{$product->id_product}}, {{$product->id_manufacturer}})" style="text-align: right;"><button class="btn btn-success" title="ADD TO CURRENCY MODULE">ADD</button></td>
                                    </tr>                                
                                @else
                                    <tr>
                                        <td><input onKeyUp="updateWholesale({{$product->id_product}}, {{$base_price->wholesale_price}}, {{$base_price->wholesale_convertion_rate}})" style="text-align: center; width: 80px;" type="text" value="@if(isset($base_price->wholesale_price)){{number_format($base_price->wholesale_price, 2, '.', ' ')}}@else N/D @endif" name="base_price" id="wholesale_{{$product->id_product}}"></td>
                                        <td>
                                            <div>{{number_format($product->wholesale_price, 2, '.', ' ')}} €</div>
                                            <div> <span id="new_wholesale_{{$product->id_product}}"></span> </div>
                                        </td>
                                        <td><input onKeyUp="updatePrice({{$product->id_product}}, {{$base_price->price}}, {{$base_price->price_convertion_rate}}, {{$product->price * 1.23}})" style="text-align: center; width: 80px;" type="text" value="@if(isset($base_price->price)){{number_format($base_price->price, 2, '.', ' ')}}@else N/D @endif" name="base_price" id="price_{{$product->id_product}}"></td>
                                        <td>
                                            <div>{{number_format($product->price, 2, '.', ' ')}} €</div>
                                            <div> <span id="new_price_{{$product->id_product}}"></span> </div>
                                        </td>
                                        <td>
                                            <div>{{number_format($product->price * 1.23, 2, '.', ' ')}} €</div>
                                            <div> <span id="new_price_with_vat_{{$product->id_product}}"></span> </div>
                                        </td>
                                        <td><button class="btn btn-warning" onclick="updateProductPrices({{$product->id_product}}, 0)">UPDATE</button></td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card" style="margin-top: 10px; display: none;">
                    <div class="card-header" style="text-decoration: none;text-align:left;">ASSOCIATION</div>
                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body" style="text-align: left;">
                            <div>
                                <label>NAME: </label> <span>{{$product->lang->name}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card" style="margin-top: 10px; display: none;">
                    <div class="card-header" style="text-decoration: none;text-align:left;">RESOURCES</div>
                    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
                        <div class="card-body" style="text-align: left;">
                            <div>
                                <label>NAME: </label> <span>{{$product->lang->name}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
