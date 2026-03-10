<div class="ai-grid">
    @foreach($providerSettings as $provider)
        <div class="ai-card" style="margin-bottom: 0;">
            <div class="d-flex justify-content-start align-items-center mb-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#providerCredentialModal" data-ai-open-provider="{{ $provider->provider }}"><i class="fa-solid fa-pencil"></i></button>
                <strong style="margin: 0 10px;">{{ $provider->label ?: ucfirst($provider->provider) }}</strong>
            </div>

            <div class="small ai-muted mb-1">Provider: {{ $provider->provider }}</div>
            <div class="small ai-muted mb-1">Status: {{ $provider->is_active ? 'ativo' : 'inativo' }}</div>
            <div class="small ai-muted mb-1">Model: {{ $provider->default_model ?: 'n/a' }}</div>
            <div class="small ai-muted mb-3">Base URL: {{ $provider->base_url ?: 'n/a' }}</div>
        </div>
    @endforeach
</div>
