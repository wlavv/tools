@extends('layouts.app')

    @include("areas.tasks.includes.js")
    @include("areas.tasks.includes.css")

@section('content')

<div class="navbar navbar-light customPanel">
    <div class="row">    
        @foreach($tasks AS $name => $group)
            <div class="col-lg-2">
                <div class="text-center uppercase operator @if($name == 'Márcia') activeOperator @endif operator_{{$name}}" onclick="selectPanel('{{$name}}')" style="cursor: pointer;">{{$name}}</div>
            </div>
        @endforeach
        <div class="col-lg-2"></div>
        <div class="col-lg-2">
        <div class="text-center uppercase operator" style="cursor: pointer;" onclick="triggerDateModal()">
            <i class="fa-solid fa-calendar-days" style="color: dodgerblue;"></i>
        </div>
    </div>
</div>

<div class="row">    
    @foreach($tasks AS $name => $group)
        <div class="col-lg-12 operators_panel operator_panel_{{$name}}" @if($name != 'Márcia') style="display: none;" @endif>    
            <div class="navbar navbar-light customPanel">
                <table class="table table-striped table-hover text-center">
                    <tr>
                        <td colspan="@if($group[0]->type==2)3 @else 2 @endif" style="font-weight: bolder;width: 230px;font-size: 30px;text-align: right;">{{date('d')}}-{{date('m')}}-{{date('Y')}}</td>
                    </tr>
                    @foreach($group AS $task)
                        <tr>
                            <td style="vertical-align: middle;"><div style="font-size: 30px;vertical-align: middle;text-align: right;margin-right:30px;">{{$task->task}}</div></td>
                            @if($task->type==2)
                            <td style="width: 100px;vertical-align: middle;"> <img src="{{$task->image}}" style="width: 80px;border: 1px solid #000;border-radius: 5px;"> </td>
                            @endif
                            <td style="vertical-align: middle;width: 15%">
                                @if($task->type == 1)
                                    <button style="margin: 10px;" onclick="saveTask({{$task->id}}, {{$task->type}}, '{{$task->name}}', 1, {{$task->value}})" class="btn btn-success {{$task->id}}_{{$task->name}}"><i class="fa-solid fa-face-smile icons_tasks_size"></i></button>
                                    <button style="margin: 10px;" onclick="saveTask({{$task->id}}, {{$task->type}}, '{{$task->name}}', 0, 0)" class="btn btn-danger {{$task->id}}_{{$task->name}}"><i class="fa-solid fa-face-frown icons_tasks_size"></i></button>
                                @else
                                    @if($task->value > 0)
                                        <button style="margin: 10px;" onclick="saveTask({{$task->id}}, {{$task->type}}, '{{$task->name}}', 1, {{$task->value}})" class="btn btn-success {{$task->id}}_{{$task->name}}"><i class="fa-solid fa-face-smile icons_tasks_size"></i></button>
                                        <button style="margin: 10px;" onclick="saveTask({{$task->id}}, {{$task->type}}, '{{$task->name}}', 0, 0)" class="btn btn-danger {{$task->id}}_{{$task->name}}"><i class="fa-solid fa-face-frown icons_tasks_size">
                                    @else
                                    <button style="margin: 10px;" onclick="saveTask({{$task->id}}, {{$task->type}}, '{{$task->name}}', 0, 0)" class="btn btn-success {{$task->id}}_{{$task->name}}"><i class="fa-solid fa-face-smile icons_tasks_size"></i></button>
                                    <button style="margin: 10px;" onclick="saveTask({{$task->id}}, {{$task->type}}, '{{$task->name}}', 1, {{$task->value}})" class="btn btn-danger {{$task->id}}_{{$task->name}}"><i class="fa-solid fa-face-frown icons_tasks_size"></i></button>
                                    @endif
                                @endif
                                
                                <div style="display:none;color:green; font-size: 25px; font-weight: bolder;" class="message_{{$task->id}}_{{$task->name}}"><i class="fa-solid fa-check icons_tasks_size" ></i></div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endforeach
</div>


<style>
    .operator{ background-color: #ddd; color: #333; padding: 10px 0; font-size: 30px; }
    .operator:hover{ background-color: dodgerblue; color: #fff; }
    .activeOperator{ background-color: dodgerblue; color: #fff;}

    .icons_tasks_size{ font-size: 70px; }

    @media (max-width: 768px) {

        .icons_tasks_size{ font-size: 50px; }
    }

</style>

<script>

function triggerDateModal(){

    Swal.fire({
        title: 'Selecionar mês e ano',
        html:
            `<select id="swal-mes" class="swal2-input">
                ${[...Array(12).keys()].map(i =>
                    `<option value="${i+1}">${new Date(0, i).toLocaleString('pt-PT', { month: 'long' })}</option>`
                ).join('')}
            </select>
            <select id="swal-ano" class="swal2-input">
                ${[2025, 2026].map(ano =>
                    `<option value="${ano}">${ano}</option>`
                ).join('')}
            </select>`,
        showCancelButton: true,
        confirmButtonText: 'Ver calendário',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const mes = document.getElementById('swal-mes').value;
            const ano = document.getElementById('swal-ano').value;
            if (!mes || !ano) {
                Swal.showValidationMessage('Por favor seleciona mês e ano');
            }
            return { mes, ano };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const { mes, ano } = result.value;
            window.location.href = `/taks/calendar/${ano}/${mes}`;
        }
    });
}

function selectPanel(operator){

    $('.activeOperator').removeClass('activeOperator');
    $('.operator_' + operator).addClass('activeOperator');

    $('.operators_panel').css('display', 'none');
    $('.operator_panel_' + operator).css('display', 'block');
    

}
 
function saveTask(id, type, name, done, value){

        /** 
        Swal.fire({
            title: 'CONFIRMA ACÇÃO?',
            showDenyButton: true,
            showCancelButton: false,
            confirmButtonText: 'SIM!',
            denyButtonText: 'NÃO',
        }).then((result) => {
            if (result.isConfirmed) {
        **/
                $.ajax({
                    url: '{{ route("tasks.updateDone") }}',
                    type: 'POST',
                    data: {
                        id: id,
                        type: type,
                        name: name,
                        done: done,
                        value: value,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        if (response.success) {

                            $('.' + id + '_' + name).remove();
                            $('.message_' + id + '_' + name).css('display', 'block');   

                            //Swal.fire("Alteração aplicada!", "", "success");
                        }
                    },
                    error: function () {
                        Swal.fire("Erro ao atualizar tarefa.", "", "error");
                    }
                });             
        /**
            } else if (result.isDenied) {
            
                
            }
        })
        **/
    }

</script>

@endsection