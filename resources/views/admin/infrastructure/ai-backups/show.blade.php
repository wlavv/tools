@extends('layouts.app')

@section('content')
    <style>
        .ai-backups-shell{display:flex;flex-direction:column;gap:14px;min-width:0}
        .ai-backups-card{border:1px solid var(--bs-border-color);border-radius:5px;background:var(--bs-body-bg);padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.08);min-width:0}
        .ai-backups-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
        .ai-backups-eyebrow{display:block;font-size:11px;text-transform:uppercase;font-weight:900;color:#d4a017;letter-spacing:.08em}
        .ai-backups-actions{display:flex;gap:8px;flex-wrap:wrap}
        .ai-backups-actions .btn{display:inline-flex;align-items:center;gap:7px;border-radius:5px;font-weight:800}
        .ai-backups-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
        .ai-backups-kv{border:1px solid var(--bs-border-color);border-radius:5px;padding:12px;min-width:0}
        .ai-backups-kv span{display:block;color:var(--bs-secondary-color);font-size:12px;text-transform:uppercase;font-weight:900}
        .ai-backups-kv strong{display:block;margin-top:4px;word-break:break-word}
        .ai-backups-log{max-height:520px;overflow:auto;border-radius:5px;background:#0f172a;color:#dbeafe;padding:12px;font-size:12px}
        @media(max-width:980px){.ai-backups-grid{grid-template-columns:1fr}}
    </style>

    <div class="ai-backups-shell">
        <div class="ai-backups-card">
            <div class="ai-backups-head">
                <div>
                    <span class="ai-backups-eyebrow">AI Server Backup</span>
                    <h1 class="h4 mb-1">{{ $filename }}</h1>
                    <p class="text-muted mb-0">Detalhe, checksum, manifest e acoes protegidas.</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @foreach($errors as $error)
            <div class="alert alert-warning">{{ $error }}</div>
        @endforeach

        <div class="ai-backups-card">
            <div class="ai-backups-grid">
                <div class="ai-backups-kv"><span>Ficheiro</span><strong>{{ $filename }}</strong></div>
                <div class="ai-backups-kv"><span>Tamanho</span><strong>{{ data_get($details, 'size') ? number_format(((float) data_get($details, 'size')) / 1024 / 1024, 2, ',', ' ') . ' MB' : '-' }}</strong></div>
                <div class="ai-backups-kv"><span>Estado</span><strong>{{ data_get($details, 'status', data_get($checksum, 'status', '-')) }}</strong></div>
                <div class="ai-backups-kv"><span>Checksum SHA256</span><strong>{{ data_get($checksum, 'checksum', data_get($details, 'checksum', '-')) }}</strong></div>
                <div class="ai-backups-kv"><span>Criado em</span><strong>{{ data_get($details, 'created_at', '-') }}</strong></div>
                <div class="ai-backups-kv"><span>Validacao</span><strong>{{ data_get($checksum, 'valid') === true ? 'OK' : data_get($checksum, 'message', '-') }}</strong></div>
            </div>
        </div>

        @php
            $manifestContent = data_get($manifest, 'manifest', data_get($manifest, 'content', 'Manifest indisponivel.'));

            if (is_array($manifestContent)) {
                $manifestContent = implode("\n", array_map(
                    fn ($line) => is_scalar($line)
                        ? (string) $line
                        : json_encode($line, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    $manifestContent
                ));
            }
        @endphp

        <div class="ai-backups-card">
            <div class="ai-backups-head mb-2">
                <div>
                    <span class="ai-backups-eyebrow">Manifest</span>
                    <h2 class="h5 mb-0">Conteudo do manifest</h2>
                </div>
            </div>
            <pre class="ai-backups-log">{{ $manifestContent }}</pre>
        </div>
    </div>
@endsection
