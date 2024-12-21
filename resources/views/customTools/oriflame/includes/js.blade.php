<script>

    function newOrder(){

        $('#orderContainer').css('display', 'none');
        $('#newOrderContainer').css('display', 'block');

    }

    function displayOrder(){

        $.ajax({
            type: 'POST',
            url: '{{route("oriflame.displayOrder")}}',
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                $('#orderContainer').replaceWith(response);
            }       
        });

    }

    
    function setCustomer(email){
        $('#customer').val(email);
        searchForCustomer();
    }
     
    function searchForCustomer(){

        let customer = $('#customer').val();

        $.ajax({
            type: 'POST',
            url: '{{route("oriflame.getCustomerInfo")}}',
            data: {
                customer: customer,
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {

                if(response.type == 'none'){
                    $('#customersList').replaceWith(response.html);
                    $('#customerOrderInfo').css('display', 'none');                    
                    $('#orderDetails').css('display', 'none');
                }

                if(response.type == 'list'){
                    $('#customersList').replaceWith(response.html);
                    $('#customerOrderInfo').css('display', 'none');                    
                    $('#orderDetails').css('display', 'none');
                }

                if(response.type == 'details'){
                    $('#customerOrderInfo').replaceWith(response.html);
                    $('#customersList').css('display', 'none');

                    $('#orderDetails').css('display', 'block');
                }

            }       
        });

    }



     
    function searchForProduct(){

        let reference = $('#productReference').val();

        $.ajax({
            type: 'POST',
            url: '{{route("oriflame.getProductInfo")}}',
            data: {
                reference: reference,
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {

                if(response.type == 'none'){
                    $('#noProductFound').replaceWith(response.html);
                    $('#createOrderButton').css('display', 'none');
                }else{
                    $('#newRow').replaceWith(response.html);
                    $('#createOrderButton').css('display', 'block');
                    $('#productReference').val('');
                    $('#productReference').focus();
                }
                /**
                if(response.type == 'list'){
                    $('#customersList').replaceWith(response.html);
                    $('#customerOrderInfo').css('display', 'none');                    
                    $('#orderDetails').css('display', 'none');
                }

                if(response.type == 'details'){
                    $('#customerOrderInfo').replaceWith(response.html);
                    $('#customersList').css('display', 'none');

                    $('#orderDetails').css('display', 'block');
                }
                    **/


                //$('#newRow').replaceWith(response);
            }       
        });

    }

function removeRow(reference){
    $('#tr_' + reference).remove();
}
</script>