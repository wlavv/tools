@extends('layouts.app')

@section('content')
    <style>
        .ai-backups-shell{display:flex;flex-direction:column;gap:14px;min-width:0}
        .ai-backups-card{border:1px solid var(--bs-border-color);border-radius:5px;background:var(--bs-body-bg);padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.08);min-width:0}
        .ai-backups-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
        .ai-backups-eyebrow{display:block;font-size:11px;text-transform:uppercase;font-weight:900;color:#d4a017;letter-spacing:.08em}
        .ai-backups-actions{display:flex;gap:8px;flex-wrap:wrap}
        .ai-backups-actions .btn,.ai-backups-actions select{border-radius:5px;font-weight:800}
        .ai-backups-log{max-height:70vh;overflow:auto;border-radius:5px;background:#0f172a;color:#dbeafe;padding:12px;font-size:12px}
    </style>

    <div class="ai-backups-shell">
        @if($error)
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <div class="ai-backups-card">
            <form method="GET" action="{{ route('admin.infrastructure.ai-backups.logs') }}" class="ai-backups-actions">
                <select name="type" class="form-select form-select-sm" style="max-width:180px">
                    <option value="backup" @selected($type === 'backup')>backup.log</option>
                    <option value="cron" @selected($type === 'cron')>cron-backup.log</option>
                </select>
                <select name="lines" class="form-select form-select-sm" style="max-width:140px">
                    @foreach([100, 250, 500] as $option)
                        <option value="{{ $option }}" @selected($lines === $option)>{{ $option }} linhas</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-file-lines"></i> Ver logs
                </button>
            </form>
        </div>

        <div class="ai-backups-card">
            @php
                $stringifyLogValue = function ($value) use (&$stringifyLogValue): string {
                    if ($value === null) {
                        return '';
                    }

                    if (is_scalar($value)) {
                        return (string) $value;
                    }

                    if (is_array($value)) {
                        $isList = array_keys($value) === range(0, count($value) - 1);

                        if ($isList) {
                            return implode("\n", array_filter(array_map($stringifyLogValue, $value), fn ($line) => $line !== ''));
                        }

                        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
                    }

                    return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
                };

                $content = data_get($result, 'content', data_get($result, 'logs', data_get($result, 'text', '')));
                $content = $stringifyLogValue($content);
            @endphp
            <pre class="ai-backups-log">{{ $content ?: 'Sem logs para apresentar.' }}</pre>
        </div>
    </div>
@endsection
