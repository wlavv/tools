@extends('layouts.app')

@section('content')
    @include('environment-manager::Includes.css')

    <div class="environment-manager-shell">
        <div class="environment-manager-card environment-manager-hero">
            <div>
                <h1 class="environment-manager-title">Laravel Config</h1>
                <p class="environment-manager-subtitle">Consulta read-only das chaves resolvidas pelo repositório config() do Laravel.</p>
            </div>
            <a href="{{ route('environment_manager.index') }}" class="environment-manager-btn environment-manager-btn--muted">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        @include('environment-manager::Includes._components.toolbar', [
            'action' => route('environment_manager.config'),
            'search' => $search,
        ])

        @include('environment-manager::Includes._components.config-table', [
            'title' => 'Configuração global Laravel',
            'entries' => $entries,
        ])
    </div>

    @include('environment-manager::Includes.js')
@endsection
