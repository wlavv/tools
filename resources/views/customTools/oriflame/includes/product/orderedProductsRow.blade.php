<tr id="tr_{{$detail->product->reference}}">
    <td>
        <div>
            <img class="imageHover" src="{{$detail->product->image}}" style="width: 50px;">
        </div>
        <div>
            {{$detail->product->reference}}
            <input name="reference[]" type="hidden" id="reference_{{$detail->product->reference}}" value="{{$detail->product->reference}}">
        </div>
    </td>
    <td>
        <div>
            {{$detail->product->name}}
            <input name="name[]" type="hidden" id="name_{{$detail->product->name}}" value="{{$detail->product->name}}">
        </div>
        <div style="margin-top: 10px;">
            <input name="quantity[]" onblur="updateProductQuantity({{$detail->id_order}}, {{$detail->product->id_product}}, {{$detail->product->reference}})" type="number" id="quantity_{{$detail->product->reference}}" value="{{$detail->quantity}}" style="text-align: center; width: 80px;"> X {{$detail->product->price}} €
            <input type="hidden" id="price_{{$detail->product->reference}}" value="{{str_replace(' €', '', $detail->product->price)}}">
        </div>
    </td>
    <td style="vertical-align: middle;"> 
        <i class="fa-solid fa-xmark" style="color: red; font-size: 22px;" onclick="removeRow({{$detail->id_order}}, {{$detail->product->id_product}}, '{{$detail->product->reference}}')"></i>
     </td>
</tr>
<tr id="newRow" style="display: none;">
    <td colspan="4"></td>
</tr>