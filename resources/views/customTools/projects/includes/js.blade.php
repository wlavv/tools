<script>
    document.getElementById('project_logo').addEventListener('paste', function (e) {

        setTimeout(() => {
            const url = e.target.value;
            const img = document.getElementById('project_logo_preview');
    
            if (url && isImageUrl(url)) {
                img.src = url;
                img.style.display = 'block';
            } else {
                img.src = '';
                img.style.display = 'none';
            }
        }, 100);
    });
    
    function isImageUrl(url) {
        return /\.(jpeg|jpg|gif|png|webp|svg)$/i.test(url);
    }
    
    function submeterFormulario(event) {
        event.preventDefault();
        
        const formData = $('#meuFormulario').serialize();
        
        console.log(formData);
        $.ajax({
          url: "{{ route('groupStructure.newProject') }}",
          type: 'POST',
          data: formData,
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          success: function(response) {
              document.getElementById('meuFormulario').reset();
              location.reload();
          },
          error: function(xhr, status, error) {
            console.error('Erro:', xhr.responseText);
            alert('Erro ao adicionar objetivo.');
          }
        });
    }

    function removeProject(id) {
        Swal.fire({
            title: "Tem certeza?",
            text: "Esta ação irá remover o projeto permanentemente.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sim, remover",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('groupStructure.destroy')}}",
                    type: "POST",
                    data: { id: id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#projectContainer_" + id).fadeOut(300, function() {
                                $("#projectContainer_" + id).remove();
                            });
                        } else {
                            Swal.fire(
                                "Erro!",
                                response.message || "Não foi possível excluir o projeto.",
                                "error"
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.fire(
                            "Erro!",
                            "Ocorreu um erro na comunicação com o servidor.",
                            "error"
                        );
                    }
                });
            }
        });
    }
    
    function editProject(id) {
        $.ajax({
            url: "{{ route('groupStructure.edit', ':id') }}".replace(':id', id),
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    // Preenche os campos
                    $('#project_name').val(data.project.name);
                    $('select[name="id_parent"]').val(data.project.id_parent);
                    $('#project_priority').val(data.project.priority);
                    $('#project_status').val(data.project.status);
                    $('#project_url').val(data.project.url);
                    $('#project_logo').val(data.project.logo);
    
                    // Pré-visualização do logo
                    if (data.project.logo) {
                        $('#project_logo_preview').attr('src', data.project.logo)
                            .css({
                                'display': 'block',
                                'margin': '20px auto',
                                'border': '1px solid #333',
                                'border-radius': '5px',
                                'background': '#FFF'
                            })
                            .show();
                    } else {
                        $('#project_logo_preview').hide();
                    }
    
                    // Guarda o ID no formulário (oculto)
                    if ($('#project_id').length === 0) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'project_id',
                            name: 'project_id',
                            value: id
                        }).appendTo('#meuFormulario');
                    } else {
                        $('#project_id').val(id);
                    }
    
                    // Altera o título para edição
                    $('#meuModalLabel').text('EDIT PROJECT');
    
                    // Mostra o modal
                    $('#meuModal').modal('show');
                } else {
                    Swal.fire("Erro", data.message || "Não foi possível carregar os dados do projeto.", "error");
                }
            },
            error: function() {
                Swal.fire("Erro", "Ocorreu um erro ao carregar o projeto.", "error");
            }
        });
    }
    
    function showDetails(id) {
        window.location.href = "{{ route('projectDetails.index', ':id') }}".replace(':id', id);
        
    }
    
    function showTasks(id) {
        window.location.href = "{{route('todo.index', ':id')}}".replace(':id', id);
    }
    
</script>