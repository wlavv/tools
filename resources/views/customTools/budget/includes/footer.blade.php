@if( !$isMobile ) <div class="col-lg-12"> <div class="spacer-10"></div> </div> @endif
<div class="col-lg-12">    
    <div class="navbar navbar-light customPanel" style="margin-top: 0;@if( $isMobile ) padding: 0;@endif">
        <div style="text-align: center;text-transform: uppercase;font-size: 25px;cursor: pointer;" onclick="$('#objectives').toggle();">OBJECTIVOS</div>
        <div id="objectives" style="display: none;min-height: 100px;height: 100%;text-align: center;">
            @if( !$isMobile )
            <div style="border-top: 1px solid #bbb;margin-top: 10px;padding-top: 15px;display: flex;">
                <div class="btn btn-info"       style="text-align: center;text-transform: uppercase;float: left;width: 33%;border-radius: 0px;font-size: 20px;">CURTO PRAZO<br><span style="font-size: 14px;">< 1 ano</span></div>
                <div class="btn btn-warning"    style="text-align: center;text-transform: uppercase;float: left;width: 34%;border-radius: 0px;font-size: 20px;">MÉDIO PRAZO<br><span style="font-size: 14px;">< 5 anos</span></div>
                <div class="btn btn-danger"     style="text-align: center;text-transform: uppercase;float: left;width: 33%;border-radius: 0px;font-size: 20px;">LONGO PRAZO<br><span style="font-size: 14px;">> 5 anos</span></div>
            </div>
            @endif
            <div id="objectives_short"  style="float: left;@if( $isMobile ) width: 100%; @else width: 33%; @endif"> 
                @if( $isMobile )<div class="btn btn-info"       style="text-align: center;text-transform: uppercase;float: left;width: 100%;border-radius: 0px;font-size: 20px;">CURTO PRAZO<br><span style="font-size: 14px;">< 1 ano</span></div>@endif
                <div>
                    <table class="table table-striped" style="width: 100%;text-align: center;margin-bottom: 0;display: table;">
                        <tr>
                            <td style="min-width: 50%;">NAME</td>
                            <td style="min-width: 25%;">VALUE</td>
                            <td style="min-width: 25%;">PERCENTAGE</td>
                            <td style="min-width: 25%;">AVAILABLE</td>
                        </tr>
                        @if( count($objectives_short) > 0)
                            @foreach($objectives_short AS $short)
                                <tr onclick="editObjectiveRow({{$short}})">
                                    <td>
                                        @if(strlen($short->link) > 0)
                                            <a style="text-decoration: none; color: green;" href="{{$short->link}}" target="_blank" title="Objective link">{{$short->name}}</a>
                                        @else
                                            {{$short->name}}
                                        @endif
                                        
                                        @if( isset( $short->buy ) && ( $short->buy == 1 ))
                                            <span style="color: dodgerblue;text-transform: uppercase">
                                                <br> {{$short->category}} @if($short->category != 'outros')- {{$short->sub_category}}@endif
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{number_format($short->amount, 2, '.', ' ')}} €</td>
                                    <td>@if( isset( $short->available )) {!!$short->available!!} @else - @endif</td>
                                    <td>@if( isset( $short->buy ) && ( $short->buy == 1 )) <span style="color: green;cursor: pointer;" onclick="setObjectiveAsDone({{$short->id}})">YES</span> @else <span style="color: red;">NO</span> @endif</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4">NO OBJECTIVES SET!</td>
                            </tr>
                        @endif
                    </table>
                </div> 
                <button class="btn btn-success" type="button" onclick="addObjective(1)" data-bs-toggle="modal" data-bs-target="#meuModal" style="width: 150px; margin: 10px 0;"> ADD OBJECTIVE</button>
            </div>
            <div id="objectives_medium" style="float: left;@if( $isMobile ) width: 100%; @else width: 34%; @endif"> 
                @if( $isMobile ) <div class="btn btn-warning"    style="text-align: center;text-transform: uppercase;float: left;width: 100%;border-radius: 0px;font-size: 20px;">MÉDIO PRAZO<br><span style="font-size: 14px;">< 5 anos</span></div> @endif
                <div>
                    <table class="table table-striped" style="width: 100%;text-align: center;margin-bottom: 0;display: table;">
                        <tr>
                            <td style="min-width: 50%;">NAME</td>
                            <td style="min-width: 25%;">VALUE</td>
                            <td style="min-width: 25%;">PERCENTAGE</td>
                            <td style="min-width: 25%;">AVAILABLE</td>
                        </tr>
                        @if( count($objectives_medium) > 0)
                            @foreach($objectives_medium AS $medium)
                                <tr onclick="editObjectiveRow({{$medium}})">
                                    <td>
                                        @if(strlen($medium->link) > 0)
                                            <a style="text-decoration: none; color: green;" href="{{$medium->link}}" target="_blank" title="Objective link">{{$medium->name}}</a>
                                        @else
                                            {{$medium->name}}
                                        @endif
                                        
                                        @if( isset( $medium->buy ) && ( $medium->buy == 1 ))
                                            <span style="color: dodgerblue;text-transform: uppercase">
                                                <br> {{$medium->category}} @if($medium->category != 'outros')- {{$medium->sub_category}}@endif
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{number_format($medium->amount, 2, '.', ' ')}} €</td>
                                    <td>@if( isset( $medium->available )) {!!$medium->available!!} @else - @endif</td>
                                    <td>@if( isset( $medium->buy ) && ( $medium->buy == 1 )) <span style="color: green;cursor: pointer;" onclick="setObjectiveAsDone({{$medium->id}})">YES</span> @else <span style="color: red;">NO</span> @endif</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4">NO OBJECTIVES SET!</td>
                            </tr>
                        @endif
                    </table>
                </div>
                <button class="btn btn-success" type="button" onclick="addObjective(2)" data-bs-toggle="modal" data-bs-target="#meuModal" style="width: 150px; margin: 10px 0;"> ADD OBJECTIVE</button> 
            </div>
            <div id="objectives_long"   style="float: left;@if( $isMobile ) width: 100%; @else width: 33%; @endif">  
                @if( $isMobile ) <div class="btn btn-danger"     style="text-align: center;text-transform: uppercase;float: left;width: 100%;border-radius: 0px;font-size: 20px;">LONGO PRAZO<br><span style="font-size: 14px;">> 5 anos</span></div> @endif
                <div>
                    <table class="table table-striped" style="width: 100%;text-align: center;margin-bottom: 0;display: table;">
                        <tr>
                            <td style="min-width: 50%;">NAME</td>
                            <td style="min-width: 25%;">VALUE</td>
                            <td style="min-width: 25%;">PERCENTAGE</td>
                            <td style="min-width: 25%;">AVAILABLE</td>
                        </tr>
                        @if( count($objectives_long) > 0)
                            @foreach($objectives_long AS $long)
                                <tr onclick="editObjectiveRow({{$long}})">
                                    <td>
                                        @if(strlen($long->link) > 0)
                                            <a style="text-decoration: none; color: green;" href="{{$long->link}}" target="_blank" title="Objective link">{{$long->name}}</a>
                                        @else
                                            {{$long->name}}
                                        @endif
                                        
                                        @if( isset( $long->buy ) && ( $long->buy == 1 ))
                                            <span style="color: dodgerblue;text-transform: uppercase">
                                                <br> {{$long->category}} @if($long->category != 'outros')- {{$long->sub_category}}@endif
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{number_format($long->amount, 2, '.', ' ')}} €</td>
                                    <td>@if( isset( $long->available )) {!!$long->available!!} @else - @endif</td>
                                    <td>@if( isset( $long->buy ) && ( $long->buy == 1 )) <span style="color: green;cursor: pointer;" onclick="setObjectiveAsDone({{$long->id}})">YES</span> @else <span style="color: red;">NO</span> @endif</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4">NO OBJECTIVES SET!</td>
                            </tr>
                        @endif
                    </table>
                </div>
                <button class="btn btn-success" type="button" onclick="addObjective(3)" data-bs-toggle="modal" data-bs-target="#meuModal" style="width: 150px; margin: 10px 0;"> ADD OBJECTIVE</button> 
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="meuModal" tabindex="-1" aria-labelledby="meuModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="meuFormulario" onsubmit="submeterFormulario(event)">
        <div class="modal-header">
          <h5 class="modal-title" id="meuModalLabel">Formulário</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="objective_id" name="objective_id">
          <input type="hidden" id="objective_type" name="objective_type">

          <!-- Outros campos do formulário -->
          <div class="mb-3">
            <label for="objective_name" class="form-label">Name</label>
            <input type="text" class="form-control" id="objective_name" name="objective_name" required>
          </div>
          <div class="mb-3">
            <label for="objective_value" class="form-label">Value</label>
            <input type="text" class="form-control" id="objective_value" name="objective_value" required>
          </div>
          <div class="mb-3">
            <label for="objective_link" class="form-label">Link</label>
            <input type="text" class="form-control" id="objective_link" name="objective_link">
          </div>
          <div class="mb-3">
            <label for="objective_priority" class="form-label">Priority</label>
            <input type="number" class="form-control" id="objective_priority" name="objective_priority" required>
          </div>
          <div class="mb-3">
            <label for="nome" class="form-label">Income source</label>
            <select class="form-control" name="objective_income_source" id="objective_income_source">
                <option value="excedente|orcamento">EXCEDENTE ORÇAMENTO</option>
                <option value="potes|auto">POTES - AUTOMÓVEIS</option>
                <option value="potes|ferias">POTES - FÉRIAS</option>
                <option value="potes|saude">POTES - SAÚDE</option>
                <option value="potes|extras">POTES - EXTRAS</option>
                <option value="potes|meninas">POTES - MENINAS</option>
                <option value="potes|casa">POTES - CASA</option>
                <option value="potes|ls">POTES - LS</option>
                <option value="outros">ORIGEM ALTERNATIVA</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    function addObjective(type) {
        document.getElementById('objective_type').value = type;
    }
    
    
    function submeterFormulario(event) {
        event.preventDefault();
        
        const formData = $('#meuFormulario').serialize();
        
        console.log(formData);
        $.ajax({
          url: "{{ route('budget.addObjective') }}",
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
    
    
    function setObjectiveAsDone(id) {
    
        Swal.fire({
            title: 'Marcar objectivo como concluido?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('budget.setObjectiveAsDone') }}",
                    type: 'POST',
                    data: {
                        id: id
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },success: function(response) {
                        location.reload();
                    },error: function(xhr, status, error) {
                        console.error('Erro:', xhr.responseText);
                        alert('Erro ao atualizar objetivo.');
                    }
                });
            }
        });
    }
  

    function editObjectiveRow(objective) {

        $('#objective_id').val(objective.id);
        $('#objective_type').val(objective.type);
        $('#objective_name').val(objective.name);
        $('#objective_link').val(objective.link);
        $('#objective_value').val(objective.amount);
        $('#objective_priority').val(objective.priority);
        
        if(objective.sub_category.length > 0){
            selected_option = objective.category + '|' + objective.sub_category;
        }else{
            selected_option = objective.category
        }

        $('#objective_income_source').val(selected_option);

        const modal = new bootstrap.Modal(document.getElementById('meuModal'));
        modal.show();
    
    }
  
</script>

