@extends('layouts.app')

@section('content')
    @include('environment-manager::Includes.css')

    <div class="environment-manager-shell">
        <div class="environment-manager-card environment-manager-hero">
            <div>
                <h1 class="environment-manager-title">Configs dos módulos</h1>
                <p class="environment-manager-subtitle">Consulta de module.json, Config/*.php e, se configurado, configs de módulos guardadas na base de dados do B.O.</p>
            </div>
            <a href="{{ route('environment_manager.index') }}" class="environment-manager-btn environment-manager-btn--muted">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        @include('environment-manager::Includes._components.toolbar', [
            'action' => route('environment_manager.modules'),
            'search' => $search,
        ])

        @if(count($modules) > 0)
            <div class="environment-manager-module-list">
                @foreach($modules as $module)
                    <a href="{{ route('environment_manager.modules.show', $module['key']) }}" class="environment-manager-card environment-manager-module-card">
                        <div class="environment-manager-module-card__header">
                            <div>
                                <h3 class="environment-manager-module-card__title">{{ $module['name'] ?? $module['key'] }}</h3>
                                <div class="environment-manager-module-card__meta">{{ $module['slug'] ?? $module['key'] }}</div>
                            </div>
                            <span class="environment-manager-badge {{ !empty($module['enabled']) ? 'environment-manager-badge--success' : 'environment-manager-badge--warning' }}">
                                {{ !empty($module['enabled']) ? 'ativo' : 'inativo' }}
                            </span>
                        </div>

                        <div class="environment-manager-module-card__meta">
                            {{ $module['description'] ?? 'Sem descrição.' }}
                        </div>

                        <div>
                            <span class="environment-manager-badge environment-manager-badge--muted">{{ count($module['configs'] ?? []) }} configs</span>
                            @foreach(($module['sources'] ?? []) as $source)
                                <span class="environment-manager-badge environment-manager-badge--muted">{{ $source }}</span>
                            @endforeach
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="environment-manager-alert environment-manager-alert--warning">
                Não foram encontrados módulos ou configs para o filtro atual.
            </div>
        @endif
    </div>

    @include('environment-manager::Includes.js')
@endsection
