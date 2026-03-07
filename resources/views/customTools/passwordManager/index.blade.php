@extends('layouts.app')

    @include("customTools.passwordManager.includes.js")
    @include("customTools.passwordManager.includes.css")

@section('content')

    <div class="row">    
        <div class="col-lg-12">    
            <div class="navbar navbar-light customPanel">
                <table class="table table-striped table-hover text-center">
                    <tr>
                        <td>LINK | SERVICE</td>
                        <td>USERNAME / EMAIL</td>
                        <td>PASSWORD</td>
                        <td></td>
                    </tr>
                    @if( count($credentials) > 0)
                        @foreach($credentials AS $credential)
                            <tr>
                                <td>
                                    <span class="showProject_{{$credential->id}}"> {{$credential->project}} </span>
                                    <input style="display: none;" class="text-center" id="passwordManager_edit_project_{{$credential->id}}" value="{{$credential->project}}">
                                </td>
                                <td>
                                    <button class="showUsername_{{$credential->id}} btn btn-secondary btn-sm" onclick="copyToClipboard('{{ $credential->username }}')"> <i class="fa fa-copy"></i> </button>
                                    <span   class="showUsername_{{$credential->id}}"> {{$credential->username}} </span>
                                    <input  style="display: none;" class="text-center" id="passwordManager_edit_username_{{$credential->id}}" value="{{$credential->username}}">
                                </td>
                                <td>
                                    <button class="showPassword_{{$credential->id}} btn btn-secondary btn-sm" onclick="copyToClipboard('{{ $credential->password }}')"> <i class="fa fa-copy"></i> </button>
                                    <span   class="blur-password showPassword_{{$credential->id}}"> {{$credential->password}} </span>
                                    <input  style="display: none;" class="text-center" id="passwordManager_edit_password_{{$credential->id}}" value="">
                                </td>
                                <td>
                                    <button class="btn btn-warning" style="padding: 10px;" onclick="editRow({{$credential->id}})" id="editCredential_{{$credential->id}}"> <i class="fa fa-pencil" aria-hidden="true"></i> </button>
                                    <button class="btn btn-success" style="padding: 10px; display: none;" onclick="submitEditCredentials({{$credential->id}})" id="updateCredential_{{$credential->id}}" style="display: none;" onclick="editRow({{$credential->id}})"> <i class="fa-solid fa-floppy-disk"></i> </button>
                                    <button class="btn btn-danger"  style="padding: 10px;" onclick="submitDestroyCredentials({{$credential->id}})">  <i class="fa-solid fa-trash-can"></i> </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                        <tr>
                            <td><input type="text" class="text-center" id="passwordManager_project" value="" style="width: 95% !important;"></td>
                            <td><input type="text" class="text-center" id="passwordManager_username" value="" style="width: 95% !important;"></td>
                            <td><input type="password" class="text-center" id="passwordManager_password" value="" style="width: 95% !important;"></td>
                            <td><button class="btn btn-success" style="padding: 10px;" onclick="submitAddCredentials()">  <i class="fa fa-plus" aria-hidden="true"></i> </button></td>
                        </tr>
                </table>
            </div>
        </div>   
    </div> 
    
@endsection
