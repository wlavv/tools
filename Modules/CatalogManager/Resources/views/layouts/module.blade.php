@extends('layouts.app')

@section('content')
    @include('catalogmanager::Includes.css')

    <div class="catalog-lsg-shell">
        @include('catalogmanager::partials.nav')

        @yield('catalogmanager-content')
    </div>

    @include('catalogmanager::Includes.js')
@endsection
