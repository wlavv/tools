@extends('layouts.app')

@section('content')
@php
    $healthData = $health['data'] ?? [];
    $responseText = data_get($result, 'response')
        ?? data_get($result, 'message')
        ?? data_get($result, 'content')
        ?? data_get($result, 'text');
@endphp

<style>
    .lsg-ai-shell,
    .lsg-ai-shell *{box-sizing:border-box}
    .lsg-ai-shell{display:grid;gap:14px;width:100%;max-width:100%;min-width:0;overflow:hidden}
    .lsg-ai-panel,
    .lsg-ai-metric{min-width:0;border:1px solid var(--border-soft,rgba(180,194,214,.16));background:linear-gradient(180deg,var(--bg-panel-soft,var(--card-bg,#424f5d)) 0%,var(--bg-panel,var(--card-bg,#3a4652)) 100%);color:var(--text-primary,#f0f4f9);padding:16px;box-shadow:0 8px 24px rgba(0,0,0,.12)}
    .lsg-ai-status-row{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;min-width:0}
    .lsg-ai-summary{display:flex;align-items:center;gap:10px;min-width:0}
    .lsg-ai-summary i{width:34px;height:34px;display:grid;place-items:center;flex:0 0 34px;border:1px solid rgba(96,165,250,.26);background:rgba(96,165,250,.12);color:#60a5fa}
    .lsg-ai-summary strong{display:block;font-size:.94rem;font-weight:900;overflow-wrap:anywhere}
    .lsg-ai-muted{display:block;color:var(--text-muted,#9aa7b8);font-size:.82rem;margin-top:2px;overflow-wrap:anywhere}
    .lsg-ai-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;min-width:0}
    .lsg-ai-metric{padding:12px}
    .lsg-ai-metric span{display:block;color:var(--text-muted,#9aa7b8);font-size:11px;font-weight:900;letter-spacing:.05em;text-transform:uppercase}
    .lsg-ai-metric strong{display:block;margin-top:4px;font-size:.94rem;overflow-wrap:anywhere;word-break:break-word}
    .lsg-ai-status{display:inline-flex;align-items:center;gap:7px;padding:7px 10px;font-weight:900;border:1px solid rgba(148,163,184,.28);white-space:nowrap}
    .lsg-ai-status.ok{background:rgba(34,197,94,.12);color:#86efac;border-color:rgba(34,197,94,.26)}
    .lsg-ai-status.bad{background:rgba(239,68,68,.12);color:#fca5a5;border-color:rgba(239,68,68,.26)}
    .lsg-ai-form{display:grid;gap:10px;min-width:0}
    .lsg-ai-section-label{display:block;color:#d4a017;font-size:11px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;margin-bottom:10px}
    .lsg-ai-label{font-weight:900;font-size:13px}
    .lsg-ai-textarea{display:block;width:100%;max-width:100%;min-height:130px;border:1px solid rgba(148,163,184,.45);padding:11px 12px;background:var(--bg-panel-soft,var(--card-bg,#424f5d));color:inherit;resize:vertical}
    .lsg-ai-actions{display:flex;gap:8px;flex-wrap:wrap;min-width:0}
    .lsg-ai-btn{border:1px solid #111827;background:#111827;color:#fff;padding:9px 12px;font-weight:900;display:inline-flex;align-items:center;gap:8px}
    .lsg-ai-output{max-width:100%;white-space:pre-wrap;overflow-wrap:anywhere;word-break:break-word;line-height:1.55;border:1px solid rgba(148,163,184,.24);background:rgba(148,163,184,.08);padding:12px}
    .lsg-ai-error{background:rgba(239,68,68,.12);color:#fca5a5;border:1px solid rgba(239,68,68,.26);padding:10px 12px;font-weight:800;overflow-wrap:anywhere}
    .lsg-ai-json{max-width:100%;max-height:360px;overflow:auto;font-size:12px;margin:0;white-space:pre-wrap;overflow-wrap:anywhere}
    .lsg-ai-interface-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px}
    .lsg-ai-interface{display:grid;gap:8px;min-width:0;border:1px solid rgba(148,163,184,.24);background:rgba(148,163,184,.08);padding:12px;color:inherit;text-decoration:none}
    .lsg-ai-interface:hover{border-color:rgba(96,165,250,.45);background:rgba(96,165,250,.10);color:inherit;text-decoration:none}
    .lsg-ai-interface__head{display:flex;align-items:center;gap:9px;min-width:0}
    .lsg-ai-interface__head i{color:#60a5fa}
    .lsg-ai-interface strong,.lsg-ai-interface span{overflow:hidden;text-overflow:ellipsis}
    .lsg-ai-interface span{color:var(--text-muted,#9aa7b8);font-size:.82rem;line-height:1.35}
    @media(max-width:575.98px){.lsg-ai-grid{grid-template-columns:1fr}.lsg-ai-status{white-space:normal}.lsg-ai-btn{width:100%;justify-content:center}}
</style>

<div class="lsg-ai-shell">
    <section class="lsg-ai-panel">
        <div class="lsg-ai-status-row">
            <div class="lsg-ai-summary">
                <i class="fa-solid fa-brain"></i>
                <div>
                    <strong>{{ $healthData['service'] ?? 'LSG AI Gateway' }}</strong>
                    <span class="lsg-ai-muted">Health do gateway local e teste rapido de geracao.</span>
                </div>
            </div>
            <span class="lsg-ai-status {{ ($health['ok'] ?? false) ? 'ok' : 'bad' }}">
                <i class="fa-solid {{ ($health['ok'] ?? false) ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                {{ ($health['ok'] ?? false) ? 'Online' : 'Offline' }}
            </span>
        </div>
    </section>

    <section class="lsg-ai-grid">
        <div class="lsg-ai-metric"><span>Gateway</span><strong>{{ $config['gateway_url'] }}</strong></div>
        <div class="lsg-ai-metric"><span>Modelo default</span><strong>{{ $healthData['default_model'] ?? $config['default_model'] }}</strong></div>
        <div class="lsg-ai-metric"><span>Timeout</span><strong>{{ $config['timeout'] }}s</strong></div>
        <div class="lsg-ai-metric"><span>Token</span><strong>{{ $config['token_configured'] ? 'Configurado' : 'Em falta' }}</strong></div>
    </section>

    @if(!($health['ok'] ?? false))
        <div class="lsg-ai-error">{{ $health['error'] }}</div>
    @endif

    <section class="lsg-ai-panel">
        <span class="lsg-ai-section-label">Interfaces AI</span>
        <div class="lsg-ai-interface-grid">
            @foreach($interfaces as $interface)
                @if(Route::has($interface['route']))
                    <a class="lsg-ai-interface" href="{{ route($interface['route']) }}">
                        <div class="lsg-ai-interface__head">
                            <i class="{{ $interface['icon'] }}"></i>
                            <strong>{{ $interface['label'] }}</strong>
                        </div>
                        <span>{{ $interface['summary'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </section>

    <section class="lsg-ai-panel">
        <form class="lsg-ai-form" method="post" action="{{ route('admin.lsg-ai.test') }}">
            @csrf
            <span class="lsg-ai-section-label">Teste</span>
            <label class="lsg-ai-label" for="prompt">Prompt de teste</label>
            <textarea id="prompt" name="prompt" class="lsg-ai-textarea" required>{{ old('prompt', $prompt) }}</textarea>
            @error('prompt')<div class="lsg-ai-error">{{ $message }}</div>@enderror
            <div class="lsg-ai-actions">
                <button class="lsg-ai-btn" type="submit"><i class="fa-solid fa-wand-magic-sparkles"></i> Testar geracao</button>
            </div>
        </form>
    </section>

    @if($error)
        <div class="lsg-ai-error">{{ $error }}</div>
    @endif

    @if($result)
        <section class="lsg-ai-panel">
            <div class="lsg-ai-status-row">
                <div class="lsg-ai-summary">
                    <i class="fa-solid fa-message"></i>
                    <div>
                        <span class="lsg-ai-section-label" style="margin-bottom:4px">Resposta</span>
                        <strong>{{ data_get($result, 'model', $config['default_model']) }}</strong>
                    </div>
                </div>
                <span class="lsg-ai-status ok">{{ data_get($result, 'status', 'ok') }}</span>
            </div>
            <div class="lsg-ai-output" style="margin-top:12px">{{ $responseText ?: json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</div>
        </section>

        <section class="lsg-ai-panel">
            <span class="lsg-ai-section-label">Payload tecnico</span>
            <pre class="lsg-ai-json">{{ json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </section>
    @endif
</div>
@endsection
