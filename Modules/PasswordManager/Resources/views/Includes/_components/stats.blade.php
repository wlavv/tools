<div class="password-manager-stats" style="width: calc( 50% - 10px); float: right;text-align: center;">
    <div class="password-manager-stat passwordManager-card">
        <strong>Total</strong>
        <div>{{ $entries->total() }}</div>
    </div>
    <div class="password-manager-stat passwordManager-card">
        <strong>Favoritos</strong>
        <div>{{ $entries->getCollection()->where('is_favorite', true)->count() }}</div>
    </div>
    <div class="password-manager-stat passwordManager-card">
        <strong>Nesta página</strong>
        <div>{{ $entries->count() }}</div>
    </div>
</div>
