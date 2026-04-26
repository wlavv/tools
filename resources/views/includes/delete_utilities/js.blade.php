<script>

    function copyToClipboard(data){

        const elementoTemporario = document.createElement("textarea");
        elementoTemporario.value = data;
        
        document.body.appendChild(elementoTemporario);
        
        elementoTemporario.select();
        elementoTemporario.setSelectionRange(0, 99999);
        
        try {
            document.execCommand("copy");

            Swal.fire({
                icon: "success",
                title: "Copied to clipboard!",
                showConfirmButton: false,
                timer: 750
            });

        } catch (err) {
            Swal.fire("Error copying to clipboard: " + err, "", "success");
        }
        
        document.body.removeChild(elementoTemporario);

    }

    function removeItem(route){

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
                        url: route,
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'delete'
                        },
                        success: function(response) {
                            location.reload();
                        }       
                    });
                }
        });
    }

    function editItem(id, data, route){
        $.ajax({
            type: 'POST',
            url: route,
            data: {
                id : id,
                active : data,
                _token: "{{ csrf_token() }}",
            },
            success: function(response) {
                
                let html = '';
                if(data == 1) html = '<i id="active_'+id+'" class="fa-solid fa-check"  style="color: green; cursor: pointer;" onclick="editItem('+id+', 0, \''+route+'\')"></i>';
                else html = '<i id="active_'+id+'" class="fa-solid fa-xmark"  style="color: red; cursor: pointer;" onclick="editItem('+id+', 1, \''+route+'\')"></i>';

                $('#active_'+id).replaceWith(html);

            }       
        });
    }

function openWhatsappOptions(id_customer){
    $('.whatsappContainer').css('display', 'none');
    $('#whatsappContainer_'+id_customer).css('display', 'contents');
}

function sendWhatsapp(phone, name, type){

    let message;
    if(type == 'backorder') message = 'Olá ' + name + ', a sua encomenda está pendente de entrega por parte do fornecedor. Assim que a entrega seja feita, procederemos a preparação da sua encomenda. Obrigado pela compreensão!';
    if(type == 'cancelled') message = 'Olá ' + name + ', a sua encomenda foi cancelada. Se precisar de mais detalhes, não hesite em contactar. Obrigado!';
    if(type == 'pickup')    message = 'Olá ' + name + ', a sua encomenda está pronta para ser levantada. Peço que nos indique onde e quando pretende fazer a recolha. Obrigado!';
    if(type == 'delivery')  message = 'Olá ' + name + ', a sua encomenda está pronta para ser entregue. Peço que nos indique onde e quando poderemos efectuar a entrega. Obrigado!';

    const win = window.open("https://wa.me/"+phone+"?text=" + encodeURIComponent(message), '_blank');    
}

function sendCustomMessage(phone){

    Swal.fire({
        title: "CUSTOM MESSAGE",
        text: "INSERT MESSAGE TO SEND TO CUSTOMER",
        input: 'text',
        showCancelButton: true        
    }).then((result) => {
        if (result.value) {
            const win = window.open("https://wa.me/"+phone+"?text=" + encodeURIComponent(result.value), '_blank'); 
        }
    });

}

function makeACall(phone){

    Swal.fire({
        icon: "phone",
        title: "Do you want to make a call?",
        showDenyButton: false,
        showCancelButton: true,
        confirmButtonText: "yes"
    }).then((result) => {
        if (result.isConfirmed) {
            window.open("tel:" + phone);
        }
    });
        
}

</script>