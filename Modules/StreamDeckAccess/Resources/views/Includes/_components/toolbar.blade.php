<div class="streamdeck-access-card sda-toolbar-card">
    <div class="sda-toolbar-header">
        <div class="sda-title-wrap">
            <span class="sda-title-icon"><i class="fa-solid fa-keyboard" aria-hidden="true"></i></span>
            <div>
                <h1 class="sda-title">Stream Deck Access</h1>
                <p class="sda-subtitle">Endpoints externos com token para atalhos, páginas e tarefas em background.</p>
            </div>
        </div>

        <a href="{{ route('streamdeck_access.create') }}" class="lsg-action-btn lsg-action-btn--primary">
            <span class="lsg-action-btn__icon"><i class="fa-solid fa-plus" aria-hidden="true"></i></span>
            <span>Novo</span>
        </a>
    </div>

    <form method="GET" action="{{ route('streamdeck_access.index') }}" class="sda-filters">
        <div class="sda-filter-field sda-filter-field--search">
            <label class="streamdeck-access-label" for="sda-q">Pesquisar</label>
            <input id="sda-q" type="search" name="q" class="streamdeck-access-input" value="{{ $filters['q'] ?? '' }}" placeholder="Nome, slug, tarefa ou URL">
        </div>

        <div class="sda-filter-field">
            <label class="streamdeck-access-label" for="sda-type-filter">Tipo</label>
            <select id="sda-type-filter" name="type" class="streamdeck-access-select">
                <option value="">Todos</option>
                @foreach(config('streamdeck-access.types', []) as $type => $label)
                    <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="sda-filter-field">
            <label class="streamdeck-access-label" for="sda-enabled-filter">Estado</label>
            <select id="sda-enabled-filter" name="enabled" class="streamdeck-access-select">
                <option value="" @selected(($filters['enabled'] ?? '') === '')>Todos</option>
                <option value="1" @selected(($filters['enabled'] ?? '') === '1')>Ativos</option>
                <option value="0" @selected(($filters['enabled'] ?? '') === '0')>Inativos</option>
            </select>
        </div>

        <div class="sda-filter-actions">
            <button type="submit" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="Filtrar">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            </button>
            <a href="{{ route('streamdeck_access.index') }}" class="lsg-action-btn lsg-action-btn--secondary lsg-action-btn--compact" title="Limpar filtros">
                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i>
            </a>
        </div>
    </form>
</div>
