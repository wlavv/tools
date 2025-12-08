<script>

    function submitAddCredentials(){

        let project  = $('#passwordManager_project').val().trim();
        let username = $('#passwordManager_username').val().trim();
        let password = $('#passwordManager_password').val().trim();

        if (project.length < 6 || username.length < 6 || password.length < 6) {

            Swal.fire({
                icon: 'warning',
                title: 'Invalid data!',
                text: 'All fields should have at leats 6 characters',
                confirmButtonText: 'OK'
            });

            return;
        }

        $.ajax({
            type: 'POST',
            url: "{{route('password_manager.actions')}}",
            data: {
                _token: "{{ csrf_token() }}",
                action : 'add',
                project : $('#passwordManager_project').val(),
                username : $('#passwordManager_username').val(),
                password : $('#passwordManager_password').val(),
            },
            success: function(response) {
                location.reload();
            }       
        });

    }

    function submitEditCredentials(id){

        let project  = $('#passwordManager_edit_project_' + id).val().trim();
        let username = $('#passwordManager_edit_username_' + id).val().trim();
        let password = $('#passwordManager_edit_password_' + id).val().trim();

        if (project.length < 6 || username.length < 6 || password.length < 6) {

            Swal.fire({
                icon: 'warning',
                title: 'Invalid data!',
                text: 'All fields should have at leats 6 characters',
                confirmButtonText: 'OK'
            });

            return;
        }

        $.ajax({
            type: 'POST',
            url: "{{route('password_manager.actions')}}",
            data: {
                _token: "{{ csrf_token() }}",
                action : 'update',
                id : id,
                project : $('#passwordManager_edit_project_' + id).val(),
                username : $('#passwordManager_edit_username_' + id).val(),
                password : $('#passwordManager_edit_password_' + id).val(),
            },
            success: function(response) {
                location.reload();
            }       
        });

    }

    function editRow(id){

        $('.showProject_' + id).toggle();
        $('.showUsername_' + id).toggle();
        $('.showPassword_' + id).toggle();

        $('#passwordManager_edit_project_' + id).toggle();
        $('#passwordManager_edit_username_' + id).toggle();
        $('#passwordManager_edit_password_' + id).toggle();

        $('#editCredential_' + id).toggle();
        $('#updateCredential_' + id).toggle();

        

    }
    
    function submitDestroyCredentials(id) {

        Swal.fire({
            title: "Are you sure?",
            text: "The record will be remove!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    type: 'POST',
                    url: "{{ route('password_manager.actions') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        action: 'destroy',
                        id: id,
                    },
                    success: function (response) {

                        Swal.fire({
                            title: "Deleted!",
                            text: "The credential was deleted.",
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    }
                });

            }
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Copiado!',
                text: 'Conteúdo copiado para a área de transferência.',
                timer: 1200,
                showConfirmButton: false
            });
        });
    }

    $(document).ready(function() {
        $('#passwordTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5, 10, 20, 50, 100],
            "order": []
        });
    });

</script>