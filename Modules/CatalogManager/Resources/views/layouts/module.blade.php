@extends('layouts.app')

@push('styles')
    @include('catalogmanager::Includes.css')
@endpush

@push('scripts')
    @include('catalogmanager::Includes.js')
@endpush

@section('content')
    <div class="catalog-lsg-shell">
        @include('catalogmanager::partials.nav')

        @yield('catalogmanager-content')
    </div>
@endsection
