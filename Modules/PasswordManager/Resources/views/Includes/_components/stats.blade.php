<div class="password-manager-stats">
    <div class="password-manager-stat">
        <strong>Total</strong>
        <div>{{ $entries->total() }}</div>
    </div>
    <div class="password-manager-stat">
        <strong>Favoritos</strong>
        <div>{{ $entries->getCollection()->where('is_favorite', true)->count() }}</div>
    </div>
    <div class="password-manager-stat">
        <strong>Nesta página</strong>
        <div>{{ $entries->count() }}</div>
    </div>
</div>
