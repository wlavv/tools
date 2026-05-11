<div class="password-manager-stats">
    <div class="password-manager-stat passwordManager-card">
        <span class="password-manager-stat__label">Total</span>
        <strong class="password-manager-stat__value">{{ $entries->count() }}</strong>
    </div>
    <div class="password-manager-stat passwordManager-card">
        <span class="password-manager-stat__label">Visible</span>
        <strong class="password-manager-stat__value">{{ $entries->count() }}</strong>
    </div>
    <div class="password-manager-stat passwordManager-card">
        <span class="password-manager-stat__label">Categories</span>
        <strong class="password-manager-stat__value">{{ $entries->pluck('category')->filter()->unique()->count() }}</strong>
    </div>
</div>
