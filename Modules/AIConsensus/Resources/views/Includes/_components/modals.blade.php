<div class="modal fade" id="providerCredentialModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('ai_consensus.credentials.save') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Credenciais provider</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Provider</label>
                            <select name="provider" id="credential_provider" class="form-control" required>
                                <option value="anthropic">anthropic</option>
                                <option value="gemini">gemini</option>
                                <option value="openai">openai</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Label</label>
                            <input type="text" name="label" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Default model</label>
                            <input type="text" name="default_model" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Base URL</label>
                            <input type="text" name="base_url" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="credential_active" checked>
                                <label class="form-check-label" for="credential_active">Ativo</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">API key</label>
                            <input type="password" name="api_key" class="form-control" autocomplete="off">
                            <div class="small ai-muted mt-1">Só é alterada se preencheres este campo.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Guardar credenciais</button>
                </div>
            </form>
        </div>
    </div>
</div>
