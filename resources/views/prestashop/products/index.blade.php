@extends('layouts.app')
@section('content')

@if( !is_null( $products))
<div class="navbar navbar-light customPanel">
    <div style="background-color: #ddd; height: 50px; padding: 5px;">
        <div style="margin: 5px;">
            <span style="border-radius: 50px; background-color: #666; color: #FFF;height: 30px; float: left;padding: 3px 5px">{{count($products)}}</span> <div style="margin: 5px 10px;float: left;">PRODUCTS</div>
        </div>
    </div>
    <table class="table table-striped text-center table-hover" style="width: 100%;">
        <thead>
            <tr>
                <td>IMAGE</td>
                <td>ID</td>
                <td>REFERENCE</td>
                <td>NAME</td>
                <td>MANUFACTURER</td>
                <td>HOUSING</td>
                <td>EAN-13</td>
                <td>PURCHASE</td>
                <td>PRICE</td>
                <td>STOCK</td>
            </tr>
        </thead>
        <tr>
            <td></td>
            <td><input type="text" id="search_id_product" onkeyup="searchFor('search_id_product')" placeholder="ID" style="text-align: center; border: 1px solid #ccc;width: 90px;"></td>
            <td><input type="text" id="search_reference" onkeyup="searchFor('search_reference')" placeholder="REFERENCE" style="text-align: center; border: 1px solid #ccc"></td>
            <td></td>
            <td></td>
            <td><input type="text" id="search_housing" onkeyup="searchFor('search_housing')" placeholder="HOUSING" style="text-align: center; border: 1px solid #ccc;width: 90px;"></td>
            <td><input type="text" id="search_ean13" onkeyup="searchFor('search_ean13')" placeholder="EAN-13" style="text-align: center; border: 1px solid #ccc;width: 120px;"></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        @foreach($products AS $product)
            <tr class="hide_row found_{{$product->id_product}} found_{{$product->reference}} found_{{$product->housing}} found_{{$product->ean13}}" onclick="openProductPreview({{$product->id_product}}, 1)">
                <td>
                    @if(isset($product->manufacturer))
                        @if(file_exists( public_path() . '/uploads/manufacturer/ASD/' . $product->manufacturer->name . '/thumb/' . $product->reference . '.jpg'))
                            <img src="https://webtools.euromuscleparts.com/uploads/manufacturer/ASD/{{$product->manufacturer->name}}/thumb/{{$product->reference}}.jpg" style="cursor: pointer;width: 75px;">
                        @else
                            <img src="https://asd.euromuscleparts.com/img/asd/150px/{{$product->id_manufacturer}}.webp" style="cursor: pointer;width: 75px;">
                        @endif
                    @else
                    @endif
                </td>
                <td>{{$product->id_product}}</td>
                <td>{{$product->reference}}</td>
                <td style="text-align: left;">{{$product->lang->name}}</td>
                <td>@if(isset($product->manufacturer)) {{$product->manufacturer->name}} @else - @endif</td>
                <td>{{$product->housing}}</td>
                <td>{{$product->ean13}}</td>
                <td>{{number_format($product->wholesale_price, 2, ',', ' ')}} €</td>
                <td>{{number_format($product->price, 2, ',', ' ')}} €</td>
                <td>{{$product->stock->quantity}}</td>
            </tr>
            @if(isset($product->manufacturer))
            <tr style="display: none;" id="product_{{$product->id_product}}" class="row_container">
                <td colspan="10"> <div id="product_container_{{$product->id_product}}"></div> </td>
            </tr>
            @endif
        @endforeach
    </table>
</div>
@else

@endif
<script>


    
    function searchFor(cell){
        
        $('.hide_row').css('display', 'none');
        $('.row_container').css('display', 'none');

        let item = $('#' + cell).val();

        console.log('[class*="found_' + item + '"]');
        
        $('[class*="found_' + item + '"]').css('display', 'table-row');
        

    }
    
    function addProductToCurrencyConverter(id_product, id_manufacturer){
        
        $.ajax({
            type: 'POST',
            url: "{{route('currencyConvertion.addSingleMissingProduct')}}",
            data: {
                id_product: id_product,
                id_manufacturer: id_manufacturer,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {

                if(response == 2){
                    Swal.fire({
                        icon: "danger",
                        title: "Please set manufacturer currencies!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                }else{
                    openProductPreview(id_product, 0);
                }
                
            }       
        });
            
    }
    
    function openProductPreview(id_product, open){
        
        $.ajax({
            type: 'POST',
            url: "{{route('products.preview')}}",
            data: {
                id_product: id_product,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                $('#product_container_' + id_product).replaceWith(response.html);
                if(open) $('#product_' + id_product).toggle();
            }       
        });
            
    }
    
    
    
    function updateProductPrices(id_product, id_product_attribute){
        
        let base_price = $('#price_' + id_product).val();
        let base_wholesale = $('#wholesale_' + id_product).val();

        $.ajax({
            type: 'POST',
            url: "{{route('products.updatePrice')}}",
            data: {
                id_product: id_product,
                id_product_attribute: id_product_attribute,
                base_price: base_price,
                base_purchase_price: base_wholesale,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                
                openProductPreview(id_product, 0);
                
                Swal.fire({
                    icon: "success",
                    title: "PRICES UPDATED SUCCESSFULLY!",
                    showConfirmButton: false,
                    timer: 1500
                });
            }       
        });

    }

    function updateWholesale( id_product, wholesale, convertion ){

        const formatter = new Intl.NumberFormat('en');        
        let base_wholesale = $('#wholesale_' + id_product).val();

        let color= 'darkgreen';
        
        if(wholesale > base_wholesale) color = 'red';
        
        $('#new_wholesale_' + id_product).replaceWith('<span id="new_wholesale_' + id_product + '" style="color: ' + color + '">' + (base_wholesale * convertion ).toFixed(2) +' €</span>');
        
    }
    
    function updatePrice( id_product, price, convertion, price_with_vat ){

        const formatter = new Intl.NumberFormat('en');        
        let base_price = $('#price_' + id_product).val();
        let base_price_with_vat = $('#price_' + id_product).val();

        let color_1= 'darkgreen';
        let color_2= 'darkgreen';
        
        if(price > base_price) color_1 = 'red';
        if(price_with_vat > ( base_price * convertion * 1.23 )) color_2 = 'red';
        
        $('#new_price_' + id_product).replaceWith('<span id="new_price_' + id_product + '" style="color: ' + color_1 + '">' + (base_price * convertion ).toFixed(2) +' €</span>');
        $('#new_price_with_vat_' + id_product).replaceWith('<span id="new_price_with_vat_' + id_product + '" style="color: ' + color_2 + '">' + (base_price * convertion * 1.23 ).toFixed(2) +' €</span>');
        
    }
</script>
<style>
    .card-body > div { padding: 5px; }
    .card-body > div > label{ font-weight: bolder; }
</style>
@endsection