@extends('layouts.app')

@section('content')
    @include('documentmanager::Includes.css')
    <div class="dms-shell">
        <div class="dms-card">
            <span class="dms-eyebrow">Document Manager safe mode</span>
            <h2>O modulo encontrou um erro controlado.</h2>
            <p>{{ $exception->getMessage() }}</p>
            <a href="{{ $diagnosticsUrl }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-stethoscope"></i> Abrir diagnostics
            </a>
        </div>
    </div>
@endsection
