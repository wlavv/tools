<div class="card shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div>
            <div class="text-muted small text-uppercase fw-bold">{{ $label }}</div>
            <div class="h3 mb-0">{{ $value ?? 0 }}{{ $suffix ?? '' }}</div>
        </div>
        <i class="{{ $icon ?? 'fa-solid fa-chart-simple' }} fa-2x text-primary"></i>
    </div>
</div>
