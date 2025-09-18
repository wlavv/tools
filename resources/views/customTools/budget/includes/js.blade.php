<script>

    function  updateForecastValue(tag){

        let year = $('#input_year').val();
        let month = $('#input_month').val();

        if (parseInt(month) >= parseInt({{date('m')}})) {
            Swal.fire({
                title: 'Confirmar alteração',
                text: 'Tens a certeza que queres alterar o valor de forecast?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sim, alterar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    Swal.fire({
                        title: 'Atualizar Orçamento',
                        input: 'number',
                        inputLabel: 'Novo valor',
                        inputPlaceholder: 'Insere o novo valor',
                        showCancelButton: true,
                        confirmButtonText: 'Atualizar',
                        cancelButtonText: 'Cancelar',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Tens de inserir um valor!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{ route('budget.updateForecastData') }}",
                                method: 'POST',
                                data: {
                                    value: value = result.value,
                                    year: year,
                                    month: month,
                                    tag: tag,
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    location.reload();
                                },
                                error: function(xhr) {
                                    console.error('Erro:', xhr.responseText);
                                }
                            });
                
                        }
                    });
                
                }
            });
        } else {
            // Mês anterior ao atual, não permite
            Swal.fire({
                title: 'Não permitido',
                text: 'Não podes alterar valores de meses anteriores.',
                icon: 'error'
            });
        }
    }
    
    function  updateValue(tag, type, group){

        Swal.fire({
            title: 'Atualizar Orçamento',
            input: 'number',
            inputLabel: 'Novo valor',
            inputPlaceholder: 'Insere o novo valor',
            showCancelButton: true,
            confirmButtonText: 'Atualizar',
            cancelButtonText: 'Cancelar',
            inputValidator: (value) => {
                if (!value) {
                    return 'Tens de inserir um valor!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let value = result.value;
                let year = $('#input_year').val();
                let month = $('#input_month').val();
                
                $('#input_' + tag).val(value);
                
                let forecast = 0;
                
                if (document.getElementById('forecast_' + group + '_' + tag)) {
                    forecast = $('#forecast_' + group + '_' + tag).val()
                }
                
                $.ajax({
                    url: "{{ route('budget.updateData') }}",
                    method: 'POST',
                    data: {
                        type: type,
                        value: value,
                        year: year,
                        month: month,
                        tag: tag,
                        group: group,
                        forecast: forecast,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {

                        if (response.status === 'success') {
                            
                            if(type =='income'){
                                $('#display_' + tag).text(
                                    Number(value).toLocaleString('pt-PT', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + ' €'
                                );
    
                                $('#input_' + tag).val(value);
                        
                                $.each(response.total, function(key, value) {
    
                                    $('#display_' + key).text(
                                        Number(value).toLocaleString('pt-PT', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }) + ' €'
                                    );
    
                                    $('#input_' + key).val(value);
                                });
                            }

                            if(type =='expense'){
                                
                                location.reload();
                            }
                            
                        } else {
                            Swal.fire('Erro', 'Ocorreu um problema ao atualizar.', 'error');
                        }
                        
    
                    },
                    error: function(xhr) {
                        console.error('Erro:', xhr.responseText);
                    }
                });
        
            }
        });
        
        
    }
    
    function formatNumberSpecial(number){
        
        return  Number(number).toLocaleString('pt-PT', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        
    }
    
    function updateDetailValue(tag, type, group){

        Swal.fire({
            title: 'Atualizar detalhe',
            html: `
                <input type="text" id="swal-input-text" class="swal2-input" placeholder="Descrição">
                <input type="number" id="swal-input-number" class="swal2-input" placeholder="Novo valor">`,
            showCancelButton: false,
            confirmButtonText: 'ADD',
            didOpen: () => {
                document.getElementById('swal-input-text').focus();
            },
            preConfirm: () => {
                const text = document.getElementById('swal-input-text').value;
                const number = document.getElementById('swal-input-number').value;
        
                if (!text || !number) {
                    Swal.showValidationMessage('Tens de preencher ambos os campos!');
                    return false;
                }
        
                return { text, number };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const textValue = result.value.text;
                const numberValue = parseFloat(result.value.number);
                let year = $('#input_year').val();
                let month = $('#input_month').val();
                
                let forecast = 0;
                
                if (document.getElementById('forecast_' + group + '_' + tag)) {
                    forecast = $('#forecast_' + group + '_' + tag).val()
                }

                $.ajax({
                    url: "{{ route('budget.updateData') }}",
                    method: 'POST',
                    data: {
                        type: type,
                        value: numberValue,
                        detail: textValue,
                        year: year,
                        month: month,
                        tag: tag,
                        forecast: forecast,
                        group: group,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        location.reload(1);
                    },
                    error: function(xhr) {
                        console.error('Erro:', xhr.responseText);
                    }
                });
                
            }
        });
    }
    
    function deleteDetail(id){
        
        Swal.fire({
            title: 'Remove row',
            text: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Remove',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: "{{ route('budget.deleteDetail') }}",
                    method: 'POST',
                    data: {
                        id_detail: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            location.reload();
                        }
                    }
                });
                
            }
        });

    }
    
    function updateDetail(id, detail, amount, tag, group){

        Swal.fire({
            title: 'Atualizar detalhe',
            html: `
                <input value="` + detail + `" type="text" id="swal-input-text" class="swal2-input" placeholder="Descrição">
                <input value="` + amount + `" type="number" id="swal-input-number" class="swal2-input" placeholder="Novo valor">`,
            showCancelButton: false,
            confirmButtonText: 'UPDATE',
            didOpen: () => {
                document.getElementById('swal-input-text').focus();
            },
            preConfirm: () => {
                const text = document.getElementById('swal-input-text').value;
                const number = document.getElementById('swal-input-number').value;
        
                if (!text || !number) {
                    Swal.showValidationMessage('Tens de preencher ambos os campos!');
                    return false;
                }
        
                return { text, number };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const textValue = result.value.text;
                const numberValue = parseFloat(result.value.number);
                let year = $('#input_year').val();
                let month = $('#input_month').val();
                
                let forecast = 0;
                
                if (document.getElementById('forecast_' + group + '_' + tag)) {
                    forecast = $('#forecast_' + group + '_' + tag).val()
                }

                $.ajax({
                    url: "{{ route('budget.updateDetail') }}",
                    method: 'POST',
                    data: {
                        id_detail: id,
                        value: numberValue,
                        detail: textValue,
                        year: year,
                        month: month,
                        tag: tag,
                        forecast: forecast,
                        group: group,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        location.reload(1);
                    },
                    error: function(xhr) {
                        console.error('Erro:', xhr.responseText);
                    }
                });
                
            }
        });

    }
</script>