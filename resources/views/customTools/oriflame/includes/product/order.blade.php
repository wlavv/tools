<div id="orderDetails" style="max-width: 600px; margin: 0 auto;">
    <div style="margin-top: 20px; border-top: 2px solid #bbb;"></div>
    <div>
        <table class="table table-hover" style="width: 100%; margin: 0 auto;margin-top: 20px;text-align: center;">
            <tr>
                <td style="width: 120px;">Reference</td>
                <td>Name</td>
                <td style="width: 50px;"></td>
            </tr>
            @foreach($order->order_details AS $detail)
                @include("customTools.oriflame.includes.product.orderedProductsRow", [ 'detail' => $detail])
            @endforeach
            <tr id="newRow" style="display: none;"> <td colspan="6"></td> </tr>
            <tr> <td colspan="3" style="background-color: #999;height: 4px;padding: 0;"></td> </tr>
            <tr>
                <td> </td>
                <td> <input id="productReference" type="text" value="" placeholder="Please insert product reference!" style="width: 60%;text-align: center;" onblur="searchForProduct()"> </td>
                <td> </td>
            </tr>
            <tr>
                <td colspan="3" style="background-color: #FFF;--bs-table-bg: #FFF;"> <div id="noProductFound"></div> </td>
            </tr>
            <tr>
                <td colspan="3">
                    <button type="button" class="btn btn-success" style="margin: 20px auto; width: 80%;" onclick="location.reload();">CREATE ORDER</button>
                </td>
            </tr>
        </table>
    </div>
</div>