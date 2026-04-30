@extends('layouts.app')

@section('content')
<div class="container-fluid notifications-page lsg-notifications-page">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">
            <i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card lsg-card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Categoria</label>
                    <select name="category" class="form-select">
                        <option value="">Todas</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo</label>
                    <select name="type" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['info','success','warning','error'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado técnico</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['queued','processing','processed','failed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Leitura</label>
                    <select name="scope" class="form-select">
                        <option value="">Todas</option>
                        <option value="unread" @selected(request('scope') === 'unread')>Não lidas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fa-solid fa-filter me-1"></i>Filtrar</button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card lsg-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 lsg-table lsg-clickable-table">
                <thead>
                    <tr>
                        <th>Notificação</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Prioridade</th>
                        <th>Leitura</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        @php
                            $recipient = $notification->recipients->first();
                            $unread = $recipient && !$recipient->read_at;
                            $typeClass = match($notification->type) {
                                'success' => 'text-bg-success',
                                'warning' => 'text-bg-warning',
                                'error' => 'text-bg-danger',
                                default => 'text-bg-primary',
                            };
                            $priorityClass = match($notification->priority) {
                                'critical', 'high' => 'text-bg-danger',
                                'medium', 'normal' => 'text-bg-warning',
                                'low' => 'text-bg-secondary',
                                default => 'text-bg-light',
                            };
                        @endphp
                        <tr class="{{ $unread ? 'lsg-row-unread' : 'lsg-row-read' }}" data-href="{{ route('notifications.show', $notification) }}" role="button" tabindex="0">
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    <div class="lsg-notification-icon">
                                        <i class="{{ $notification->icon ?: 'fa-solid fa-bell' }}"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $notification->title }}</div>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($notification->message, 120) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge text-bg-light border">{{ $notification->category }}</span></td>
                            <td><span class="badge {{ $typeClass }}">{{ $notification->type }}</span></td>
                            <td><span class="badge {{ $priorityClass }}">{{ $notification->priority }}</span></td>
                            <td>
                                @if($unread)
                                    <span class="badge text-bg-primary">Não lida</span>
                                @else
                                    <span class="badge text-bg-secondary">Lida</span>
                                @endif
                            </td>
                            <td>{{ $notification->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="text-end lsg-row-actions">
                                <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="d-inline" onclick="event.stopPropagation();" onsubmit="event.stopPropagation(); return confirm('Remover esta notificação da tua lista?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Remover notificação">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-regular fa-bell-slash d-block mb-2 fs-3"></i>
                                Sem notificações para mostrar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">
            {{ $notifications->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.lsg-clickable-table tr[data-href]').forEach(function (row) {
        row.addEventListener('click', function (event) {
            if (event.target.closest('a, button, form, input, select, textarea, .lsg-row-actions')) {
                return;
            }

            window.location.href = row.dataset.href;
        });

        row.addEventListener('keydown', function (event) {
            if (event.target.closest('a, button, form, input, select, textarea, .lsg-row-actions')) {
                return;
            }

            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                window.location.href = row.dataset.href;
            }
        });
    });
});
</script>

@endsection
