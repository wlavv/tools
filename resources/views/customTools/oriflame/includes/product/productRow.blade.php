<tr id="tr_{{$product['reference']}}">
    <td>
        <div>
            <img class="imageHover" src="{{$product['thumb']}}" style="width: 50px;">
        </div>
        <div>
            {{$product['reference']}}
            <input name="reference[]" type="hidden" id="reference_{{$product['reference']}}" value="{{$product['reference']}}">
        </div>
    </td>
    <td>
        <div>
            {{$product['title']}}
            <input name="name[]" type="hidden" id="name_{{$product['reference']}}" value="{{$product['title']}}">
        </div>
        <div style="margin-top: 10px;">
            <input name="quantity[]" type="number" id="quantity_{{$product['reference']}}" value="1" style="text-align: center; width: 80px;"> X {{$product['price']}} €
            <input type="hidden" id="price_{{$product['reference']}}" value="{{str_replace(' €', '', $product['price'])}}">
        </div>
    </td>
    <td style="vertical-align: middle;"> 
        <i class="fa-solid fa-xmark" style="color: red; font-size: 22px;" onclick="removeRow('{{$product['reference']}}')"></i>
     </td>
</tr>
<tr id="newRow" style="display: none;">
    <td colspan="4"></td>
</tr>