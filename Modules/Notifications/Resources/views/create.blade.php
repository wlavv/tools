@extends('layouts.app')

@section('content')
<div class="container-fluid notifications-page lsg-notifications-page">
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            <div class="fw-semibold mb-1">
                <i class="fa-solid fa-triangle-exclamation me-1"></i>Não foi possível enviar a notificação.
            </div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('notifications.store') }}">
        @csrf

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card lsg-card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="lsg-notification-icon">
                                <i class="fa-solid fa-message"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Mensagem</h5>
                                <div class="text-muted small">Conteúdo principal da notificação.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Título</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mensagem</label>
                            <textarea name="message" class="form-control" rows="6" required>{{ old('message') }}</textarea>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Tipo</label>
                                <select name="type" class="form-select" required>
                                    @foreach(['info' => 'Info', 'success' => 'Success', 'warning' => 'Warning', 'error' => 'Error'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('type', 'info') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Prioridade</label>
                                <select name="priority" class="form-select" required>
                                    @foreach(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'critical' => 'Critical'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('priority', 'normal') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Categoria</label>
                                <input type="text" name="category" class="form-control" value="{{ old('category', 'manual') }}" maxlength="80">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card lsg-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="lsg-notification-icon">
                                <i class="fa-solid fa-link"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Ação opcional</h5>
                                <div class="text-muted small">Botão/link apresentado na notificação.</div>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Label</label>
                                <input type="text" name="action_label" class="form-control" value="{{ old('action_label') }}" maxlength="100" placeholder="Ex: Ver tarefa">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">URL</label>
                                <input type="text" name="action_url" class="form-control" value="{{ old('action_url') }}" maxlength="500" placeholder="https://... ou rota já gerada">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Ícone Font Awesome</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon', 'fa-solid fa-bell') }}" maxlength="100">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card lsg-card mb-3">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="lsg-notification-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Destinatários</h5>
                                <div class="text-muted small">Define quem recebe a notificação.</div>
                            </div>
                        </div>

                        <div class="lsg-choice-stack">
                            <label class="lsg-choice">
                                <input type="radio" name="recipient_mode" value="me" @checked(old('recipient_mode', 'users') === 'me')>
                                <span>
                                    <strong>Apenas eu</strong>
                                    <small>Útil para testes rápidos.</small>
                                </span>
                            </label>

                            <label class="lsg-choice">
                                <input type="radio" name="recipient_mode" value="users" @checked(old('recipient_mode', 'users') === 'users')>
                                <span>
                                    <strong>Utilizadores selecionados</strong>
                                    <small>Envio direto user → user.</small>
                                </span>
                            </label>

                            <label class="lsg-choice">
                                <input type="radio" name="recipient_mode" value="all" @checked(old('recipient_mode') === 'all')>
                                <span>
                                    <strong>Todos os utilizadores</strong>
                                    <small>Broadcast interno.</small>
                                </span>
                            </label>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Utilizadores</label>
                            <select name="users[]" class="form-select" multiple size="9">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" @selected(in_array($user->id, old('users', [])))>
                                        {{ $user->name ?: ('User #' . $user->id) }}{{ $user->email ? ' · ' . $user->email : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Usado apenas quando o modo é “Utilizadores selecionados”.</div>
                        </div>
                    </div>
                </div>

                <div class="card lsg-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="lsg-notification-icon">
                                <i class="fa-solid fa-share-nodes"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Canais</h5>
                                <div class="text-muted small">Interno por defeito. Externos ficam prontos para providers.</div>
                            </div>
                        </div>

                        <div class="lsg-channel-grid">
                            @foreach($supportedChannels as $channel)
                                <label class="lsg-channel">
                                    <input type="checkbox" name="channels[]" value="{{ $channel }}" @checked(in_array($channel, old('channels', ['internal'])))>
                                    <span>{{ $channel }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input type="hidden" name="queue" value="0">
                            <input class="form-check-input" type="checkbox" name="queue" value="1" id="queueSwitch" @checked(old('queue'))>
                            <label class="form-check-label" for="queueSwitch">Enviar por queue</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-4">
                            <i class="fa-solid fa-paper-plane me-1"></i>Enviar notificação
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
