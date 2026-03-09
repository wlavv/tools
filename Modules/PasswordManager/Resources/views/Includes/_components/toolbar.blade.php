<div class="password-manager-toolbar">
    <div class="password-manager-toolbar-grid">
        <form method="GET" action="{{ route('password_manager.index') }}" style="display:grid; grid-template-columns:1fr auto; gap:0.75rem;">
            <input
                type="text"
                name="q"
                value="{{ $search ?? '' }}"
                placeholder="Pesquisar por título, email, username ou URL"
                class="password-manager-input"
            >
            <button type="submit" class="password-manager-btn password-manager-btn-primary">Pesquisar</button>
        </form>

        <div class="password-manager-actions">
            <a href="{{ route('password_manager.create') }}" class="password-manager-btn password-manager-btn-primary">Novo registo</a>
            <a href="{{ route('password_manager.index') }}" class="password-manager-btn">Limpar</a>
        </div>
    </div>
</div>
