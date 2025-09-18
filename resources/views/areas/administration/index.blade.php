@extends('layouts.app')

    @include("customTools.projects.includes.js")
    @include("customTools.projects.includes.css")
    
@section('content')

    @if(isset($projects))
        <div class="row">

            @foreach($projects AS $key => $project)
                <div class="col-lg-12" style="cursor: pointer;">
                    <div class="navbar navbar-light customPanel" style="display: flow-root;">
                        <div onclick="addProject()" data-bs-toggle="modal" data-bs-target="#meuModal" style="cursor: pointer;border: 1px solid darkgreen;padding: 7px 10px;float: left;" onclick="$('#newProject').css('display', 'block');">
                            <span><i class="fa-solid fa-plus" style="color: darkgreen; font-size: 20px;margin: 0 10px;"></i></span> 
                        </div>
                        <div style="float: left; width: calc( 100% - 70px ); margin-left: 10px;" onclick="$('#projectsContainer_{{$project['father']->id}}').toggle()" ><h3 style="padding: 5px 0 0 0;">{{$project['father']->name}}</h3></div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="row" id="projectsContainer_{{$project['father']->id}}" @if($key == 0) style="display: flex;" @else style="display: none;" @endif>
                        @if(count($project['sons']) > 0)
                            @foreach($project['sons'] AS $son)
                                <div id="projectContainer_{{$son->id}}" class="col-lg-2">
                                    <div class="navbar navbar-light customPanel" style="text-align: center;padding: 0;">
                                        <div>
                                            <div>
                                                <table style="width: 100%;text-align: center;margin-bottom: 0;display: table;">
                                                    <tr>
                                                        <td>
                                                            <div style="padding: 15px 0 0 0;font-weight: bolder;">{{$son->name}}</div>
                                                            <div  class="btn btn-success" style="border: 1px solid #bbb; border-radius: 5px;  @if($son->have_details == 1) cursor: pointer; @else cursor: default; @endif color: #FFF;padding: 0;margin: 10px;background-color: #FFF;">
                                                                
                                                                @if(strlen($son->logo) > 10)
                                                                    <img style="width: 128px;margin: 0 auto;border-radius: 5px;" src="{{$son->logo}}"  @if($son->have_details == 1) onclick="showDetails({{$son->id}})" @endif>
                                                                @else
                                                                    <img style="width: 128px;margin: 0 auto;border-radius: 5px;" src="/images/unknown.png"  @if($son->have_details == 1) onclick="showDetails({{$son->id}})" @endif>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <button class="btn btn-danger"  style="width: 23%;" onclick="removeProject({{$son->id}})"><i style="color: #FFF;" class="fa-solid fa-trash"></i></button>
                                                                <button class="btn btn-warning" style="width: 23%;" onclick="editProject({{$son->id}})"><i style="color: #FFF;" class="fa-solid fa-pencil"></i></button>
                                                                <button @if( count($son->tasks) > 0) class="btn btn-dark" style="width: 23%; padding: 2px;color: red;font-weight: bolder;" @else class="btn btn-primary" style="width: 23%; padding: 2px;" @endif onclick="showTasks({{$son->id}})">{{count($son->tasks)}}</button>
                                                                @if(strlen($son->url) > 0) <a class="btn btn-success" style="width: 23%;" href="{{$son->url}}"><i style="color: #FFF;" class="fa-solid fa-link"></i></a> @endif
                                                            </div> 
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="width: 100%; height: 10px;"> </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <div class="projectStatus {{str_replace(' ', '', $son->status)}}">{{$son->status}}</div> 
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-lg-12">
                                <div class="alert alert-warning" role="alert" style="text-align: center;"> No projects available yet! </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach    
        </div>
        
    
    <!-- Modal -->
    <div class="modal fade" id="meuModal" tabindex="-1" aria-labelledby="meuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="meuFormulario" onsubmit="submeterFormulario(event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="meuModalLabel">NEW PROJECT</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
    
                        <!-- Name -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">Name</span>
                            <input type="text" class="form-control" id="project_name" name="project_name" required>
                        </div>
    
                        <!-- Parent -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">Parent</span>
                            <select name="id_parent" class="form-control">
                                <option value="0" selected>SET AS PARENT</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project['father']->id }}">{{ $project['father']->name }}</option>
                                @endforeach
                            </select>
                        </div>
    
                        <!-- Priority -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">Priority</span>
                            <input type="number" class="form-control" id="project_priority" name="project_priority" required>
                        </div>
    
                        <!-- Status -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">Status</span>
                            <select class="form-control" name="project_status" id="project_status">
                                <option value="New">NEW</option>
                                <option value="In Progress">IN PROGRESS</option>
                                <option value="Waiting Info">WAITING INFO</option>
                                <option value="Hold">HOLD</option>
                                <option value="Done">DONE</option>
                                <option value="Cancelled">CANCELLED</option>
                            </select>
                        </div>
    
                        <!-- URL -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">URL</span>
                            <input type="text" class="form-control" id="project_url" name="project_url">
                        </div>
    
                        <!-- Logo -->
                        <div class="input-group mb-3">
                            <span class="input-group-text">Logo</span>
                            <input type="text" class="form-control" id="project_logo" name="project_logo">
                        </div>
                        <div class="text-center">
                            <img id="project_logo_preview" src="" alt="Pré-visualização do logo" style="display:none; max-width:200px; border: 1px solid #333; border-radius: 5px; background: #FFF;">
                        </div>
    
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    
@endsection