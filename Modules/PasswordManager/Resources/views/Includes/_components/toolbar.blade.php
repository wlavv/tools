<div class="pm-toolbar-grid">
    <div class="password-manager-toolbar passwordManager-card">
        <form method="GET" action="{{ route('password_manager.index') }}" class="pm-toolbar-search">
            <div class="pm-toolbar-search__field">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Search by title, email, username or URL" class="password-manager-input" style="border: 0px solid #333 !important">
            </div>
            <button type="submit" class="password-manager-btn password-manager-btn-primary">Search</button>
        </form>
    </div>
</div>
