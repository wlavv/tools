<div class="ai-grid">
    @foreach($providerSettings as $provider)
        <div class="ai-card" style="margin-bottom: 0;">
            <div class="d-flex justify-content-start align-items-center mb-2 gap-2">
                <button type="button" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" data-bs-toggle="modal" data-bs-target="#providerCredentialModal" data-ai-open-provider="{{ $provider->provider }}">
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-pencil"></i></span>
                </button>
                <strong>{{ $provider->label ?: ucfirst($provider->provider) }}</strong>
            </div>

            <div class="small ai-muted mb-1">Provider: {{ $provider->provider }}</div>
            <div class="small ai-muted mb-1">Status: {{ $provider->is_active ? 'ativo' : 'inativo' }}</div>
            <div class="small ai-muted mb-1">Model: {{ $provider->default_model ?: 'n/a' }}</div>
            <div class="small ai-muted mb-3">Base URL: {{ $provider->base_url ?: 'n/a' }}</div>
        </div>
    @endforeach
</div>
