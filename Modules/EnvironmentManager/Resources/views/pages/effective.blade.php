@extends('layouts.app')

@section('content')
    @include('environment-manager::Includes.css')

    <div class="environment-manager-shell">
        <div class="environment-manager-card environment-manager-hero">
            <div>
                <h1 class="environment-manager-title">Effective Config</h1>
                <p class="environment-manager-subtitle">Valores resolvidos para consulta: runtime env tem prioridade sobre .env; config() representa a configuração efetiva do Laravel.</p>
            </div>
            <a href="{{ route('environment_manager.index') }}" class="environment-manager-btn environment-manager-btn--muted">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        @include('environment-manager::Includes._components.toolbar', [
            'action' => route('environment_manager.effective'),
            'search' => $search,
        ])

        @include('environment-manager::Includes._components.config-table', [
            'title' => 'Effective env',
            'entries' => $effective['env'] ?? [],
            'emptyText' => 'Não foram encontrados valores efetivos de env para o filtro atual.',
        ])

        @include('environment-manager::Includes._components.config-table', [
            'title' => 'Effective Laravel config()',
            'entries' => $effective['config'] ?? [],
            'emptyText' => 'Não foram encontrados valores de configuração Laravel para o filtro atual.',
        ])
    </div>

    @include('environment-manager::Includes.js')
@endsection
