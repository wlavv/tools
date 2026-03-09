<div class="ai-grid">
    @foreach($providerSettings as $provider)
        <div class="ai-provider-box">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>{{ $provider->label ?: ucfirst($provider->provider) }}</strong>
                <span class="ai-badge">{{ $provider->is_active ? 'ativo' : 'inativo' }}</span>
            </div>
            <div class="small ai-muted mb-1">Provider: {{ $provider->provider }}</div>
            <div class="small ai-muted mb-1">Model: {{ $provider->default_model ?: 'n/a' }}</div>
            <div class="small ai-muted mb-3">Base URL: {{ $provider->base_url ?: 'n/a' }}</div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#providerCredentialModal" data-ai-open-provider="{{ $provider->provider }}">Editar credenciais</button>
        </div>
    @endforeach
</div>
