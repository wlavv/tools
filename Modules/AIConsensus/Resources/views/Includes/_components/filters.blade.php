<div class="ai-card">
    <form method="GET" action="{{ route('ai_consensus.index') }}">
        <div class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Pesquisar</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Título ou prompt">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control">
                    <option value="">Todos</option>
                    @foreach(['queued','running','integrating','done','failed'] as $statusOption)
                        <option value="{{ $statusOption }}" @selected(($filters['status'] ?? '') === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('ai_consensus.index') }}" class="btn btn-outline-secondary">Limpar</a>
            </div>
        </div>
    </form>
</div>
