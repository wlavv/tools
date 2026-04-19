<div class="password-manager-stats">
    <div class="password-manager-stat passwordManager-card">
        <span class="password-manager-stat__label">Total</span>
        <strong class="password-manager-stat__value">{{ $entries->total() }}</strong>
    </div>
    <div class="password-manager-stat passwordManager-card">
        <span class="password-manager-stat__label">Favorites</span>
        <strong class="password-manager-stat__value">{{ $entries->getCollection()->where('is_favorite', true)->count() }}</strong>
    </div>
    <div class="password-manager-stat passwordManager-card">
        <span class="password-manager-stat__label">This page</span>
        <strong class="password-manager-stat__value">{{ $entries->count() }}</strong>
    </div>
</div>
