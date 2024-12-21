<script>

    function addCustomerContainer(){

        $('#customerForm').prop('action', '{{route("customers.store")}}');

        const element = document.getElementById('spanMethod');
            
        if (element){
            element.remove();

            $('#firstname').val('');
            $('#lastname').val('');
            $('#email').val('');
            $('#phone').val('');
        }

        $('#openCustomerModal').trigger('click');


    }

    function editCustomerContainer(id_customer, route){

        $.ajax({
            type: 'GET',
            url: route,
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                $('#editCustomersContainer').replaceWith(response);

                $('#customerForm').prop('action', response.update);

                $('#firstname').val(response.firstname);
                $('#lastname').val(response.lastname);
                $('#email').val(response.email);
                $('#phone').val(response.phone);

                $('#openCustomerModal').trigger('click');

            }       
        });
    }

</script>