@extends('layouts.app')

@section('content')
<div class="container-fluid notifications-page lsg-notifications-page">
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">
            <i class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card lsg-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="lsg-notification-icon lsg-notification-icon-lg">
                            <i class="{{ $notification->icon ?: 'fa-solid fa-bell' }}"></i>
                        </div>
                        <div>
                            <h2 class="h5 mb-1">{{ $notification->title }}</h2>
                            <div class="text-muted small">{{ $notification->category }} · {{ $notification->type }} · {{ $notification->priority }}</div>
                        </div>
                    </div>

                    <div class="lsg-message-box mb-3">
                        {!! nl2br(e($notification->message)) !!}
                    </div>

                    <dl class="row mb-0 lsg-definition-list">
                        <dt class="col-sm-4">Módulo origem</dt><dd class="col-sm-8">{{ $notification->source_module ?: '-' }}</dd>
                        <dt class="col-sm-4">Referência</dt><dd class="col-sm-8">{{ $notification->reference_type ?: '-' }} / {{ $notification->reference_id ?: '-' }}</dd>
                        <dt class="col-sm-4">Ação</dt><dd class="col-sm-8">@if($notification->action_url)<a href="{{ $notification->action_url }}">{{ $notification->action_label ?: $notification->action_url }}</a>@else - @endif</dd>
                        <dt class="col-sm-4">Criada em</dt><dd class="col-sm-8">{{ $notification->created_at?->format('Y-m-d H:i:s') }}</dd>
                        <dt class="col-sm-4">Expira em</dt><dd class="col-sm-8">{{ $notification->expires_at?->format('Y-m-d H:i:s') ?: '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card lsg-card mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Destinatários</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle lsg-table mb-0">
                            <thead><tr><th>Nome</th><th>Contacto</th><th>Estado</th></tr></thead>
                            <tbody>
                                @forelse($notification->recipients as $recipient)
                                    <tr>
                                        <td>{{ $recipient->name ?: ('User #' . ($recipient->user_id ?: '-')) }}</td>
                                        <td>{{ $recipient->email ?: $recipient->phone ?: '-' }}</td>
                                        <td>
                                            @if($recipient->dismissed_at)
                                                <span class="badge text-bg-secondary">Ocultada</span>
                                            @elseif($recipient->read_at)
                                                <span class="badge text-bg-success">Lida</span>
                                            @elseif($recipient->seen_at)
                                                <span class="badge text-bg-info">Vista</span>
                                            @else
                                                <span class="badge text-bg-warning">Pendente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted text-center py-3">Sem destinatários.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card lsg-card">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted mb-3">Logs de canal</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle lsg-table mb-0">
                            <thead><tr><th>Canal</th><th>Provider</th><th>Estado</th><th>Data</th></tr></thead>
                            <tbody>
                                @forelse($notification->logs as $log)
                                    <tr>
                                        <td>{{ $log->channel }}</td>
                                        <td>{{ $log->provider ?: '-' }}</td>
                                        <td>{{ $log->status }}</td>
                                        <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center py-3">Sem logs.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
