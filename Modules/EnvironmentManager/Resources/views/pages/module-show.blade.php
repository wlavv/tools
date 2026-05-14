@extends('layouts.app')

@section('content')
    @include('environment-manager::Includes.css')

    <div class="environment-manager-shell">
        <div class="environment-manager-card environment-manager-hero">
            <div>
                <h1 class="environment-manager-title">{{ $module['name'] ?? $module['key'] }}</h1>
                <p class="environment-manager-subtitle">{{ $module['description'] ?? 'Detalhe das configurações disponíveis para este módulo.' }}</p>
            </div>
            <a href="{{ route('environment_manager.modules') }}" class="environment-manager-btn environment-manager-btn--muted">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>

        <div class="environment-manager-card">
            <div class="environment-manager-summary-grid">
                <div class="environment-manager-stat">
                    <span class="environment-manager-stat__label">Slug</span>
                    <strong class="environment-manager-stat__value">{{ $module['slug'] ?? $module['key'] }}</strong>
                </div>
                <div class="environment-manager-stat">
                    <span class="environment-manager-stat__label">Pasta</span>
                    <strong class="environment-manager-stat__value">{{ $module['folder'] ?? '—' }}</strong>
                </div>
                <div class="environment-manager-stat">
                    <span class="environment-manager-stat__label">Versão</span>
                    <strong class="environment-manager-stat__value">{{ $module['version'] ?? '—' }}</strong>
                </div>
                <div class="environment-manager-stat">
                    <span class="environment-manager-stat__label">Estado</span>
                    <strong class="environment-manager-stat__value">{{ !empty($module['enabled']) ? 'Ativo' : 'Inativo' }}</strong>
                </div>
                <div class="environment-manager-stat">
                    <span class="environment-manager-stat__label">Configs</span>
                    <strong class="environment-manager-stat__value">{{ count($module['configs'] ?? []) }}</strong>
                </div>
            </div>
        </div>

        @include('environment-manager::Includes._components.config-table', [
            'title' => 'Configurações do módulo',
            'entries' => $module['configs'] ?? [],
        ])
    </div>

    @include('environment-manager::Includes.js')
@endsection
