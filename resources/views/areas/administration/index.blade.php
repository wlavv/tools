@extends('layouts.app')
   
@section('content')

    @include('areas.administration.partials.department-dashboard-injection')
    @include('areas.partials.idea-panel', ['areaKey' => 'admin'])

@endsection
