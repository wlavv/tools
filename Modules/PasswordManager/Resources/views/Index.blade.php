@extends('layouts.app')

@section('content')
    @include('password-manager::Includes.css')

        <div class="password-manager-shell">
            @include('password-manager::Includes._components.header')

            @if(session('success'))
                <div class="password-manager-alert">{{ session('success') }}</div>
            @endif

            @include('password-manager::Includes._components.toolbar', ['search' => $search])
            @include('password-manager::Includes._components.stats', ['entries' => $entries])
            @include('password-manager::Includes._components.table', ['entries' => $entries])
        </div>

    @include('password-manager::Includes.js')
@endsection
