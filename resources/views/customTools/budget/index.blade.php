@extends('layouts.app')

    @include("customTools.budget.includes.js")
    @include("customTools.budget.includes.css")

@section('content')

    <div class="row">    
        @include("customTools.budget.includes.kpi")
        <div class="col-lg-12"></div>
        @foreach($budget->expense AS $row)
            @include("customTools.budget.includes.panel", ['title' => $row->name, 'slug' => $row->slug, 'forecast' => $row->forecast, 'row' => $row])
        @endforeach
        @include("customTools.budget.includes.footer")

    </div>
    
@endsection
