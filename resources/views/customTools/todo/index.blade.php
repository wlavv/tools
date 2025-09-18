@extends('layouts.app')

@include("customTools.todo.includes.js")
@include("customTools.todo.includes.css")

@section('content')

<div class="navbar navbar-light customPanel" style="display: flow-root;">
    <div onclick="addMainTask(0)" style="cursor: pointer;border: 1px solid darkgreen;padding: 7px 10px;float: left;">
        <span><i class="fa-solid fa-plus" style="color: darkgreen; font-size: 20px;margin: 0 10px;"></i></span> 
    </div>
    <div style="float: left; width: 200px; margin-left: 10px;"><h3 style="padding: 5px 0 0 0;">Project tasks</h3></div>
    <div onclick="$('.toHide').css('display', 'block');$('.drop-placeholder').css('display', 'block');$(this).remove()" style="cursor: pointer;border: 1px solid orange;padding: 7px 10px;float: right;">
        <span><i class="fa-solid fa-pencil" style="color: orange; font-size: 20px;margin: 0 10px;"></i></span> 
    </div>
</div>

<div class="navbar navbar-light customPanel" style="text-align: center;padding: 0;">

    @if(isset($mainTasks) && count($mainTasks) > 0 )
        <ul id="taskRoot1" class="task-list">
            @if(isset($mainTasks) && count($mainTasks) > 0)
                @foreach($mainTasks as $task)
                    @include('customTools.todo.includes.task-item', ['task' => $task])
                @endforeach
            @endif
            <li class="drop-placeholder toHide" style="border: 2px dashed #ddd; border-radius: 5px; background-color: #f9f9f9;">Drop here</li>
        </ul>

    @else
        <div id="socialMediaAlert" class="alert alert-warning" style="margin-bottom: 0;">
            ⚠️ NO TASKS TO SHOW AT THE MOMENT, ADD SOME TO START!
        </div>
    @endif
</div>

<div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">TASK INFO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="taskForm">
                <input type="hidden" name="id" id="id_task" value="">
                <input type="hidden" name="id_project" id="id_project" value="{{$id_project}}">
                <input type="hidden" name="id_parent"  id="id_parent"  value="0">
                <input type="hidden" name="status"     id="status"  value="0">
                <input type="hidden" name="priority"   id="priority" value="0">
                <div class="modal-body text-right">
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Title</label>
                        <div class="col-md-8">
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Start Date</label>
                        <div class="col-md-8">
                            <input type="date" id="start_date" name="start_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Expected Time</label>
                        <div class="col-md-8">
                            <input type="number" placeholder=" ( days ) " id="expected_time" name="expected_time" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-md-4 col-form-label">Comment</label>
                        <div class="col-md-8">
                            <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="deleteTaskBtn" class="btn" style="float: left; display: none;color: red;border: 1px solid red;" onclick="removeTask(this)">
                        <i class="fa-solid fa-trash" style="color: red; font-size: 25px;"></i>
                    </button>
                    <button class="btn" type="submit" style="float: right;border: 1px solid darkgreen;"><i class="fa-solid fa-floppy-disk" style="color: darkgreen; font-size: 25px;"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
