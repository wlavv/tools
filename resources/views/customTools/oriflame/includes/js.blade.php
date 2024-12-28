<script>

    function newOrder(){
        $('#orderContainer').css('display', 'none');
        $('#orders_list').css('display', 'none');
        $('#kpi').css('display', 'none');       
        $('#newOrderContainer').css('display', 'block');
        $('#customer').focus();
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
            url: '{{route("customer.getCustomerInfo")}}',
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
                    $('#customer').val(response.email);
                    $('#customersList').css('display', 'none');

                    $.ajax({
                        type: 'POST',
                        url: '{{route("oriflame.getOrderProducts")}}',
                        data: {
                            id_customer: response.id_customer,
                            _token: "{{ csrf_token() }}",
                        },
                        success: function(response) {
                            $('#orderDetails').replaceWith(response);  
                        }     
                    });
                }
            }       
        });

    }

    function updateProductQuantity(id_order, id_product, reference){

        $.ajax({
            type: 'POST',
            url: '{{route("oriflame.updateProductQuantity")}}',
            data: {
                id_order: id_order,
                id_product: id_product,
                quantity: $('#quantity_' + reference).val(),
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
            }     
        });

    }

     
    function searchForProduct(id_customer){

        let reference = $('#productReference').val();

        $.ajax({
            type: 'POST',
            url: '{{route("oriflame.getProductInfo")}}',
            data: {
                reference: reference,
                id_customer: $('#selected_customer').val(),
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
            }       
        });

    }

function removeRow(id_order, id_product, reference){

    Swal.fire({
        icon: "error",
        title: "Confirm deletion of this record?",
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "REMOVE"
    }).then((result) => {
        if (result.isConfirmed) {

            $.ajax({
                type: 'POST',
                url: '{{route("oriflame.removeProduct")}}',
                data: {
                    id_order: id_order,
                    id_product: id_product,
                    _token: "{{ csrf_token() }}",
                },
                success: function(response) {
                    $('#tr_' + reference).remove();
                }     
            });
        }
    });

}

function changeOrderStatus(id_order, id_status){

    $.ajax({
        type: 'POST',
        url: '{{route("oriflame.changeOrderStatus")}}',
        data: {
            id_order: id_order,
            id_status: id_status,
            _token: "{{ csrf_token() }}",
        },
        success: function(response) {
            location.reload();
        }     
    });
}


</script>