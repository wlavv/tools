@php
    $counts = $overview['counts'] ?? [];
@endphp

<div class="environment-manager-card">
    <div class="environment-manager-section-title">
        <h2>Resumo do ambiente</h2>
        <span class="environment-manager-badge environment-manager-badge--success">read-only</span>
    </div>

    <div class="environment-manager-summary-grid">
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">Ambiente</span>
            <strong class="environment-manager-stat__value">{{ $overview['app_env'] ?? '—' }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">Debug</span>
            <strong class="environment-manager-stat__value">{{ !empty($overview['app_debug']) ? 'Ativo' : 'Inativo' }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">.env</span>
            <strong class="environment-manager-stat__value">{{ !empty($overview['env_file_readable']) ? 'Legível' : 'Não disponível' }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">Config cache</span>
            <strong class="environment-manager-stat__value">{{ !empty($overview['config_cached']) ? 'Ativa' : 'Inativa' }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">Módulos</span>
            <strong class="environment-manager-stat__value">{{ $counts['modules'] ?? 0 }}</strong>
        </div>
    </div>

    <div class="environment-manager-summary-grid" style="margin-top: .75rem;">
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">Laravel</span>
            <strong class="environment-manager-stat__value">{{ $overview['laravel_version'] ?? '—' }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">PHP</span>
            <strong class="environment-manager-stat__value">{{ $overview['php_version'] ?? '—' }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">.env keys</span>
            <strong class="environment-manager-stat__value">{{ $counts['env_file'] ?? 0 }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">Runtime keys</span>
            <strong class="environment-manager-stat__value">{{ $counts['runtime_env'] ?? 0 }}</strong>
        </div>
        <div class="environment-manager-stat">
            <span class="environment-manager-stat__label">Config keys</span>
            <strong class="environment-manager-stat__value">{{ $counts['laravel_config'] ?? 0 }}</strong>
        </div>
    </div>
</div>
