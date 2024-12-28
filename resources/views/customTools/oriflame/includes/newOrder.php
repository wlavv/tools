<div class="row" style="margin-top: 10px;">
    <div class="col-lg-5"></div>
    <div class="col-lg-2">
        <div>
            <label style="width: 100%; text-align: center; padding-right: 5px; text-transform: uppercase; font-weight: bolder;">Customer: </label> 
        </div>
        <div>
            <input style="width: 100%; text-align: center;" type="text" name="customer" id="customer" onblur="searchForCustomer()" placeholder="Enter e-mail or phone number" autofocus>
        </div>
    </div>
    <div class="col-lg-5"></div>

    <div class="col-lg-4"></div>
    <div class="col-lg-4">
        <div id="customerOrderInfo"> </div>
    </div>
    <div class="col-lg-4"></div>
    <div class="col-lg-12"> <div id="customersList"></div> </div>   
    
    <div class="col-lg-12" style="margin-bottom: 10px;">    
        <div id="orderDetails" style="display: none; max-width: 600px; margin: 0 auto;">
            <div style="margin-top: 20px; border-top: 2px solid #bbb;"></div>
            <div>
                <table class="table table-hover" style="width: 100%; margin: 0 auto;margin-top: 20px;text-align: center;">
                    <tr>
                        <td style="width: 120px;">Reference</td>
                        <td>Name</td>
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
</div>