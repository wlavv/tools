@extends('layouts.app')

@section('content')
<div class="container-fluid notifications-page lsg-notifications-page">
    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card lsg-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('notifications.settings.save') }}" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Canal</label>
                            <select name="channel" class="form-select" required>
                                @foreach(['email','whatsapp','discord','sms','webhook'] as $channel)
                                    <option value="{{ $channel }}">{{ $channel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Provider</label>
                            <input type="text" name="provider" class="form-control" placeholder="twilio / generic_webhook / webhook" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Settings JSON</label>
                            <textarea name="settings_json" rows="12" class="form-control" placeholder='{"webhook_url":"https://..."}'></textarea>
                            @error('settings_json')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 form-check ms-2">
                            <input type="hidden" name="enabled" value="0">
                            <input class="form-check-input" type="checkbox" value="1" name="enabled" id="enabledProvider" checked>
                            <label class="form-check-label" for="enabledProvider">Ativo</label>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card lsg-card mb-3">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Exemplos rápidos de configuração</h2>
<pre class="small mb-2">email / smtp_laravel
{}
</pre>
<pre class="small mb-2">sms / twilio
{
  "account_sid": "ACxxxx",
  "auth_token": "xxxx",
  "from": "+1..."
}
</pre>
<pre class="small mb-2">whatsapp / twilio
{
  "account_sid": "ACxxxx",
  "auth_token": "xxxx",
  "from": "+14155238886"
}
</pre>
<pre class="small mb-0">discord / webhook
{
  "webhook_url": "https://discord.com/api/webhooks/..."
}
</pre>
                </div>
            </div>
            <div class="card lsg-card">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-muted">Providers configurados</h2>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead><tr><th>Canal</th><th>Provider</th><th>Ativo</th><th>Settings</th></tr></thead>
                            <tbody>
                                @forelse($configs as $config)
                                    <tr>
                                        <td>{{ $config->channel }}</td>
                                        <td>{{ $config->provider }}</td>
                                        <td>{!! $config->enabled ? '<span class="badge text-bg-success">Sim</span>' : '<span class="badge text-bg-secondary">Não</span>' !!}</td>
                                        <td><pre class="small mb-0">{{ json_encode($config->settings, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">Sem providers configurados.</td></tr>
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
