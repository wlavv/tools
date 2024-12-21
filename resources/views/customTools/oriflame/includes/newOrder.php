<div class="row" style="margin-top: 10px;">
    <div class="col-lg-6">
        <label style="width: 100px; text-align: right; padding-right: 5px;">Customer: </label> <input style="width: calc(100% - 105px)" type="text" name="customer" id="customer" onblur="searchForCustomer()" placeholder="Enter e-mail or phone number" autofocus>
    </div>

    <div class="col-lg-6">
        <div id="customerOrderInfo"> </div>
    </div>

    <div class="col-lg-12"> <div id="customersList"></div> </div>   
    
    <div class="col-lg-12">    
        <div id="orderDetails" style="display: none;">
            <div style="margin-top: 20px; border-top: 2px solid #bbb;"></div>
            <div>
                <table class="table table-striped" style="width: 95%; margin: 0 auto;margin-top: 20px;text-align: center;">
                    <tr>
                        <td style="width: 120px;">Reference</td>
                        <td style="width: 100px;">Image</td>
                        <td style="width: 100px;">Quantity</td>
                        <td>Name</td>
                        <td style="width: 100px;">RRP</td>
                        <td style="width: 50px;"></td>
                    </tr>
                    <tr id="newRow" style="display: none;"> <td colspan="6"></td> </tr>
                    <tr> <td colspan="6" style="background-color: #999;height: 4px;padding: 0;"></td> </tr>
                    <tr>
                        <td> <input id="productReference" type="text" value="" style="width: 100px;text-align: center;" onblur="searchForProduct()"> </td>
                        <td colspan="5"> <div id="noProductFound"></div> </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-12" style="text-align: center;" id="createOrderButton"> 
        <button type="button" class="btn btn-success" style="margin: 20px auto;">CREATE ORDER</button>
    </div>   

</div>