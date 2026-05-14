@extends('layouts.app')

@section('content')
    @include('environment-manager::Includes.css')

    <div class="environment-manager-shell">
        <div class="environment-manager-card environment-manager-hero">
            <div>
                <h1 class="environment-manager-title">Environment Manager</h1>
                <p class="environment-manager-subtitle">
                    Consulta read-only do ambiente atual, ficheiro .env, runtime env, configuração Laravel e configurações individuais dos módulos registados no B.O.
                </p>
            </div>
            <span class="environment-manager-badge environment-manager-badge--success">
                <i class="fa-solid fa-lock"></i>
                Apenas consulta
            </span>
        </div>

        <div class="environment-manager-grid">
            <a href="{{ route('environment_manager.env') }}" class="environment-manager-card environment-manager-nav-card">
                <span class="environment-manager-nav-card__icon"><i class="fa-solid fa-file-lines"></i></span>
                <div class="environment-manager-nav-card__title">.env / Runtime</div>
                <div class="environment-manager-nav-card__text">Consultar valores do ficheiro .env e variáveis efetivas do processo.</div>
            </a>

            <a href="{{ route('environment_manager.config') }}" class="environment-manager-card environment-manager-nav-card">
                <span class="environment-manager-nav-card__icon"><i class="fa-solid fa-gears"></i></span>
                <div class="environment-manager-nav-card__title">Config Laravel</div>
                <div class="environment-manager-nav-card__text">Ver chaves resolvidas pelo repositório config() do Laravel.</div>
            </a>

            <a href="{{ route('environment_manager.modules') }}" class="environment-manager-card environment-manager-nav-card">
                <span class="environment-manager-nav-card__icon"><i class="fa-solid fa-cubes"></i></span>
                <div class="environment-manager-nav-card__title">Módulos</div>
                <div class="environment-manager-nav-card__text">Ler module.json e ficheiros Config/*.php de cada módulo.</div>
            </a>

            <a href="{{ route('environment_manager.effective') }}" class="environment-manager-card environment-manager-nav-card">
                <span class="environment-manager-nav-card__icon"><i class="fa-solid fa-layer-group"></i></span>
                <div class="environment-manager-nav-card__title">Effective Config</div>
                <div class="environment-manager-nav-card__text">Ver valores efetivos e prioridade entre runtime env e .env.</div>
            </a>
        </div>

        @include('environment-manager::Includes._components.summary', ['overview' => $overview])

        <div class="environment-manager-card">
            <div class="environment-manager-section-title">
                <h2>Notas de segurança</h2>
            </div>
            <div class="environment-manager-muted">
                Este módulo não cria, altera nem remove configurações. Chaves sensíveis como passwords, tokens, secrets, API keys, APP_KEY e connection strings são mascaradas automaticamente.
            </div>
        </div>
    </div>

    @include('environment-manager::Includes.js')
@endsection
