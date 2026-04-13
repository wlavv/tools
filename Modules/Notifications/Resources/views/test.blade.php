@extends('layouts.app')

@section('content')
<div class="container-fluid notifications-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-1">Consola de teste</h1>
            <div class="text-muted small">Página para validar canais, providers e logs do módulo.</div>
        </div>
        <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Voltar</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form method="POST" action="{{ route('notifications.test.send') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Título</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', 'Teste de notificação') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo</label>
                            <select name="type" class="form-select">
                                @foreach(['info','success','warning','error'] as $type)
                                    <option value="{{ $type }}" @selected(old('type', 'info') === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prioridade</label>
                            <select name="priority" class="form-select">
                                @foreach(['low','normal','high'] as $priority)
                                    <option value="{{ $priority }}" @selected(old('priority', 'normal') === $priority)>{{ ucfirst($priority) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mensagem</label>
                            <textarea name="message" rows="4" class="form-control" required>{{ old('message', 'Esta é uma notificação de teste enviada pelo módulo Notifications.') }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria</label>
                            <input type="text" name="category" class="form-control" value="{{ old('category', 'tests') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">User ID</label>
                            <input type="number" name="user_id" class="form-control" value="{{ old('user_id', auth()->id()) }}">
                        </div>
                        <div class="col-md-4 form-check mt-4 pt-2 ms-2">
                            <input type="hidden" name="queue" value="0">
                            <input type="checkbox" class="form-check-input" name="queue" value="1" id="queueFlag" @checked(old('queue', '0') == '1')>
                            <label class="form-check-label" for="queueFlag">Forçar queue</label>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nome destinatário</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', auth()->user()->name ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+3519...">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Discord webhook URL do destinatário</label>
                            <input type="url" name="discord_webhook_url" class="form-control" value="{{ old('discord_webhook_url') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label d-block">Canais</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($supportedChannels as $channel)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="channels[]" value="{{ $channel }}" id="channel_{{ $channel }}" @checked(in_array($channel, old('channels', config('notifications.test_default_channels', ['internal']))))>
                                        <label class="form-check-label" for="channel_{{ $channel }}">{{ $channel }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Assunto email</label>
                            <input type="text" name="email_subject" class="form-control" value="{{ old('email_subject', 'Teste do módulo Notifications') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mensagem SMS</label>
                            <input type="text" name="sms_message" class="form-control" value="{{ old('sms_message', 'SMS de teste do módulo Notifications.') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Corpo email</label>
                            <textarea name="email_body" rows="4" class="form-control">{{ old('email_body', 'Este email valida o canal email do módulo Notifications.') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mensagem WhatsApp</label>
                            <textarea name="whatsapp_message" rows="4" class="form-control">{{ old('whatsapp_message', 'WhatsApp de teste do módulo Notifications.') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mensagem Discord</label>
                            <textarea name="discord_message" rows="3" class="form-control">{{ old('discord_message', "Teste Discord
Mensagem enviada a partir do módulo Notifications.") }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Webhook URL</label>
                            <input type="url" name="webhook_url" class="form-control" value="{{ old('webhook_url') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Método</label>
                            <select name="webhook_method" class="form-select">
                                @foreach(['POST','PUT','PATCH'] as $method)
                                    <option value="{{ $method }}" @selected(old('webhook_method', 'POST') === $method)>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Headers JSON</label>
                            <input type="text" name="webhook_headers_json" class="form-control" value='{{ old('webhook_headers_json', '{"Authorization":"Bearer token"}') }}'>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Payload JSON</label>
                            <textarea name="webhook_payload_json" rows="4" class="form-control">{{ old('webhook_payload_json', '{"custom":"value"}') }}</textarea>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-paper-plane"></i> Enviar teste</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Checks rápidos</h2>
                    <ul class="small mb-0 ps-3">
                        <li>Internal: aparece no sino e em <code>/notifications</code>.</li>
                        <li>Email: precisa de mailer Laravel funcional.</li>
                        <li>SMS e WhatsApp: precisam de provider configurado.</li>
                        <li>Discord: usa webhook.</li>
                        <li>Se <strong>queue</strong> estiver ativo, confirma worker a correr.</li>
                    </ul>
                </div>
            </div>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Providers atuais</h2>
                    @forelse($configs as $channel => $items)
                        <div class="mb-3">
                            <div class="fw-semibold mb-1">{{ $channel }}</div>
                            @foreach($items as $item)
                                <div class="small d-flex justify-content-between border rounded px-2 py-1 mb-1">
                                    <span>{{ $item->provider }}</span>
                                    <span>{!! $item->enabled ? '<span class="badge text-bg-success">ativo</span>' : '<span class="badge text-bg-secondary">off</span>' !!}</span>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-muted small">Ainda não existem providers configurados.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
