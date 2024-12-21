<tr id="tr_{{$product['reference']}}">
    <td>
        {{$product['reference']}}
        <input name="reference[]" type="hidden" id="reference_{{$product['reference']}}" value="{{$product['reference']}}">
    </td>
    <td>
        <img class="imageHover" src="{{$product['thumb']}}" style="width: 50px;">
    </td>
    <td>
        <input name="quantity[]" type="number" id="quantity_{{$product['reference']}}" value="1" style="text-align: center; width: 80px;">
    </td>
    <td>
        {{$product['title']}}
        <input name="name[]" type="hidden" id="name_{{$product['reference']}}" value="{{$product['title']}}">
    </td>
    <td>
        {{$product['price']}}
        <input type="hidden" id="price_{{$product['reference']}}" value="{{str_replace(' €', '', $product['price'])}}">
    </td>
    <td> <i class="fa-solid fa-xmark" style="color: red; font-size: 22px;" onclick="removeRow('{{$product['reference']}}')"></i> </td>
</tr>
<tr id="newRow" style="display: none;">
    <td colspan="4"></td>
</tr>