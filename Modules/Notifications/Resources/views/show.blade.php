@extends('layouts.app')

@section('content')
<div class="container-fluid notifications-page">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">{{ $notification->title }}</h1>
            <div class="text-muted small">{{ $notification->category }} · {{ $notification->type }} · {{ $notification->priority }}</div>
        </div>
        <div class="btn-group">
            <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Voltar</a>
            <form method="POST" action="{{ route('notifications.markRead', $notification) }}">
                @csrf
                <button type="submit" class="btn btn-outline-success"><i class="fa-solid fa-check"></i> Marcar lida</button>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Mensagem</h2>
                    <div class="mb-3">{!! nl2br(e($notification->message)) !!}</div>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Módulo origem</dt><dd class="col-sm-8">{{ $notification->source_module ?: '-' }}</dd>
                        <dt class="col-sm-4">Referência</dt><dd class="col-sm-8">{{ $notification->reference_type ?: '-' }} / {{ $notification->reference_id ?: '-' }}</dd>
                        <dt class="col-sm-4">Ação</dt><dd class="col-sm-8">@if($notification->action_url)<a href="{{ $notification->action_url }}">{{ $notification->action_label ?: $notification->action_url }}</a>@else - @endif</dd>
                        <dt class="col-sm-4">Criada em</dt><dd class="col-sm-8">{{ $notification->created_at?->format('Y-m-d H:i:s') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Destinatários</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Nome</th><th>Email / Telefone</th><th>Estado</th></tr></thead>
                            <tbody>
                                @foreach($notification->recipients as $recipient)
                                    <tr>
                                        <td>{{ $recipient->name ?: ('User #' . ($recipient->user_id ?: '-')) }}</td>
                                        <td>{{ $recipient->email ?: $recipient->phone ?: '-' }}</td>
                                        <td>
                                            @if($recipient->dismissed_at)
                                                Dismissed
                                            @elseif($recipient->read_at)
                                                Lida
                                            @elseif($recipient->seen_at)
                                                Vista
                                            @else
                                                Pendente
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Logs de canal</h2>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Canal</th><th>Provider</th><th>Estado</th><th>Data</th></tr></thead>
                            <tbody>
                                @foreach($notification->logs as $log)
                                    <tr>
                                        <td>{{ $log->channel }}</td>
                                        <td>{{ $log->provider ?: '-' }}</td>
                                        <td>{{ $log->status }}</td>
                                        <td>{{ $log->created_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
