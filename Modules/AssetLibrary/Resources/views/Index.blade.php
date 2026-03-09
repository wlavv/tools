@extends('layouts.app')

@section('content')
    @include('asset-library::Includes.css')
    @include('asset-library::Includes.js')

    <div class="al-page">
        @include('asset-library::Includes._components.flash')
        @include('asset-library::Includes._components.toolbar', ['filters' => $filters, 'types' => $types, 'statuses' => $statuses])
        @include('asset-library::Includes._components.stats', ['stats' => $stats])
        @include('asset-library::Includes._components.grid', ['assets' => $assets])
    </div>
@endsection
