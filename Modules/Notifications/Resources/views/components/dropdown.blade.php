<div class="dropdown notifications-dropdown" data-notifications-dropdown data-url="{{ route('notifications.dropdownData') }}" data-polling="{{ (int) config('notifications.polling_seconds', 30) }}">
    <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fa-solid fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill text-bg-danger d-none" data-notifications-badge>0</span>
    </button>
    <div class="dropdown-menu dropdown-menu-end p-0 shadow border-0" style="width: 360px; max-width: calc(100vw - 24px);">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <div class="fw-semibold">Notificações</div>
                <div class="small text-muted">Atualização automática</div>
            </div>
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">Abrir centro</a>
        </div>
        <div data-notifications-list class="list-group list-group-flush">
            <div class="p-3 text-muted small">A carregar...</div>
        </div>
    </div>
</div>
