@extends('layouts.app')

@section('content')
    @include('password-manager::Includes.css')

    <div class="password-manager-shell">

        @if($errors->any())
            <div class="password-manager-alert password-manager-alert--warning">
                <strong>There are validation errors in the form.</strong>
            </div>
        @endif

        @include('password-manager::Includes._components.form', [
            'entry' => $entry,
            'revealed' => $revealed,
            'action' => $action,
            'method' => $method,
        ])
    </div>

    @include('password-manager::Includes.js')
@endsection
