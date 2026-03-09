<div class="al-toolbar al-panel">
    <form method="GET" action="{{ route('asset_library.index') }}" class="al-toolbar-form">
        <div class="al-field">
            <label for="search">Pesquisar</label>
            <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="al-input" placeholder="nome, ficheiro, tags..." />
        </div>

        <div class="al-field">
            <label for="type">Tipo</label>
            <select id="type" name="type" class="al-select">
                <option value="">Todos</option>
                @foreach($types as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="al-field">
            <label for="status">Estado</label>
            <select id="status" name="status" class="al-select">
                <option value="">Todos</option>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="al-actions">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="{{ route('asset_library.index') }}" class="btn btn-outline-secondary">Limpar</a>
        </div>

        <div class="al-actions" style="justify-content:flex-end;">
            <a href="{{ route('asset_library.create') }}" class="btn btn-primary">Novo asset</a>
        </div>
    </form>
</div>
