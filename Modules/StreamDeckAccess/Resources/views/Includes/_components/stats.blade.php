@php
    $total = $accessPoints->count();
    $enabled = $accessPoints->where('enabled', true)->count();
    $tasks = $accessPoints->where('type', 'task')->count();
    $redirects = $accessPoints->where('type', 'redirect')->count();
@endphp

<div class="sda-stats-grid prm-dashboard-grid">
    <div class="prm-dashboard-metric roles">
        <div>
            <div class="prm-dashboard-metric__label">Total</div>
            <div class="prm-dashboard-metric__value">{{ $total }}</div>
        </div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-table-cells-large" aria-hidden="true"></i></div>
    </div>
    <div class="prm-dashboard-metric users">
        <div>
            <div class="prm-dashboard-metric__label">Ativos</div>
            <div class="prm-dashboard-metric__value">{{ $enabled }}</div>
        </div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></div>
    </div>
    <div class="prm-dashboard-metric permissions">
        <div>
            <div class="prm-dashboard-metric__label">Tarefas</div>
            <div class="prm-dashboard-metric__value">{{ $tasks }}</div>
        </div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-gears" aria-hidden="true"></i></div>
    </div>
    <div class="prm-dashboard-metric critical">
        <div>
            <div class="prm-dashboard-metric__label">Redirects</div>
            <div class="prm-dashboard-metric__value">{{ $redirects }}</div>
        </div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></div>
    </div>
</div>
