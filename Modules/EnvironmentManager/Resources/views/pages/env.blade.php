@extends('layouts.app')

@section('content')
    @include('environment-manager::Includes.css')

    <div class="environment-manager-shell">
        <div class="environment-manager-card environment-manager-hero">
            <div>
                <h1 class="environment-manager-title">.env / Runtime Env</h1>
                <p class="environment-manager-subtitle">Consulta dos valores definidos no ficheiro .env e das variáveis presentes no processo PHP.</p>
            </div>
            <a href="{{ route('environment_manager.index') }}" class="environment-manager-btn environment-manager-btn--muted">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        @include('environment-manager::Includes._components.toolbar', [
            'action' => route('environment_manager.env'),
            'search' => $search,
        ])

        @include('environment-manager::Includes._components.config-table', [
            'title' => '.env file',
            'entries' => $envFileEntries,
            'emptyText' => 'Não foi possível ler o ficheiro .env ou não existem chaves para o filtro atual.',
        ])

        @include('environment-manager::Includes._components.config-table', [
            'title' => 'Runtime env',
            'entries' => $runtimeEntries,
            'emptyText' => 'Não existem variáveis runtime para o filtro atual.',
        ])
    </div>

    @include('environment-manager::Includes.js')
@endsection
