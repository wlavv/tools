<div class="password-manager-toolbar passwordManager-card" style="width: calc( 50% - 10px); float: left;">
    <div class="password-manager-toolbar-grid" style="display: contents">
        <form method="GET" action="{{ route('password_manager.index') }}" style="display:grid; grid-template-columns:1fr auto; gap:0.75rem; width: 75%; float: left;">
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Pesquisar por título, email, username ou URL" class="password-manager-input" >
            <button type="submit" class="password-manager-btn password-manager-btn-primary">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
        <div class="password-manager-actions" style="width: 6%; float: right; height: 48px;text-align: right;">
            <a href="{{ route('password_manager.create') }}" class="password-manager-btn password-manager-btn-primary">
                <i class="fa-solid fa-plus"></i>
            </a>
        </div>
    </div>
</div>
