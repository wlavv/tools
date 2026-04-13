@extends('layouts.app')

@section('content')
<div class="container-fluid notifications-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Centro de notificações</h1>
            <div class="text-muted small">Gestão central de notificações internas e multi-canal.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @foreach(($actions ?? []) as $action)
                <a href="{{ $action['url'] }}" class="{{ $action['class'] }}">{!! $action['icon'] !!} <span class="ms-1">{{ $action['name'] }}</span></a>
            @endforeach
            <form method="POST" action="{{ route('notifications.markAllRead') }}">
                @csrf
                <button type="submit" class="btn btn-outline-success"><i class="fa-solid fa-check-double"></i><span class="ms-1">Marcar tudo</span></button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
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
                    <label class="form-label">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['queued','processing','processed','failed'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Âmbito</label>
                    <select name="scope" class="form-select">
                        <option value="">Todas</option>
                        <option value="unread" @selected(request('scope') === 'unread')>Não lidas</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary w-100"><i class="fa-solid fa-filter"></i> Filtrar</button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Categoria</th>
                        <th>Tipo</th>
                        <th>Prioridade</th>
                        <th>Estado</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        @php $recipient = $notification->recipients->first(); @endphp
                        <tr class="{{ $recipient && !$recipient->read_at ? 'table-light' : '' }}">
                            <td>
                                <div class="fw-semibold">{{ $notification->title }}</div>
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($notification->message, 110) }}</div>
                            </td>
                            <td>{{ $notification->category }}</td>
                            <td><span class="badge text-bg-light border">{{ $notification->type }}</span></td>
                            <td>{{ $notification->priority }}</td>
                            <td>{{ $notification->status }}</td>
                            <td>{{ $notification->created_at?->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="{{ route('notifications.show', $notification) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                                    <form method="POST" action="{{ route('notifications.markRead', $notification) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('notifications.dismiss', $notification) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Sem notificações para mostrar.</td>
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
@endsection
