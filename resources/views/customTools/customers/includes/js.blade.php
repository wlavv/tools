<script>

function addCustomerContainer(){

    $('#defaultCustomersContainer').css('display', 'none');
    $('#editCustomersContainer').css('display', 'none');
    $('#addCustomersContainer').css('display', 'block');

}

function editCustomerContainer(id_customer, route){

    $('#defaultCustomersContainer').css('display', 'none');
    $('#addCustomersContainer').css('display', 'none');

    $.ajax({
        type: 'GET',
        url: route,
        data: {
            _token: "{{ csrf_token() }}",
        },
        success: function(response) {
            $('#editCustomersContainer').replaceWith(response);
        }       
    });

}


</script>