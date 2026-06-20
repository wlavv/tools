@extends('layouts.app')
   
@section('content')

    @include('areas.administration.partials.department-dashboard-injection')
    @include('areas.partials.product-growth-work-panel', ['areaKey' => 'administration'])
    @include('areas.partials.idea-panel', ['areaKey' => 'admin'])

@endsection
