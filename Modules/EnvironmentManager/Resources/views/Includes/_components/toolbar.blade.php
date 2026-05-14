<div class="environment-manager-card environment-manager-toolbar">
    <form method="GET" action="{{ $action }}" class="environment-manager-search">
        <input
            type="text"
            name="q"
            value="{{ $search ?? '' }}"
            placeholder="Pesquisar por chave, valor, origem, módulo ou ficheiro"
            class="environment-manager-input"
            data-environment-manager-autofocus
        >
        <button type="submit" class="environment-manager-btn">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Pesquisar</span>
        </button>
    </form>

    @if(!empty($search))
        <a href="{{ $action }}" class="environment-manager-btn environment-manager-btn--muted">
            <i class="fa-solid fa-xmark"></i>
            <span>Limpar</span>
        </a>
    @endif
</div>
