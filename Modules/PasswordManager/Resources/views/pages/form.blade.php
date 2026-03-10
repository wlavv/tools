@extends('layouts.app')

@section('content')
    @include('password-manager::Includes.css')

    <div class="password-manager-page passwordManager-card">
        <div class="password-manager-shell">
            @include('password-manager::Includes._components.header')

            @if($errors->any())
                <div class="password-manager-alert" style="background:#fff7ed; border-color:#fdba74; color:#9a3412;">
                    <strong>Existem erros no formulário.</strong>
                </div>
            @endif

            @include('password-manager::Includes._components.form', [
                'entry' => $entry,
                'revealed' => $revealed,
                'action' => $action,
                'method' => $method,
            ])
        </div>
    </div>

    @include('password-manager::Includes.js')
@endsection
