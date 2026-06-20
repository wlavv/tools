@extends('layouts.app')

@section('content')
    <style>
        .ai-backups-shell{display:flex;flex-direction:column;gap:14px;min-width:0}
        .ai-backups-card{border:1px solid var(--bs-border-color);border-radius:5px;background:var(--bs-body-bg);padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.08);min-width:0}
        .ai-backups-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
        .ai-backups-eyebrow{display:block;font-size:11px;text-transform:uppercase;font-weight:900;color:#d4a017;letter-spacing:.08em}
        .ai-backups-actions{display:flex;gap:8px;flex-wrap:wrap}
        .ai-backups-actions .btn{display:inline-flex;align-items:center;gap:7px;border-radius:5px;font-weight:800}
        .ai-backups-table-wrap{overflow-x:auto;max-width:100%}
        .ai-backups-table{width:100%;border-collapse:separate;border-spacing:0}
        .ai-backups-table th,.ai-backups-table td{padding:10px;border-bottom:1px solid var(--bs-border-color);vertical-align:middle;white-space:nowrap}
        .ai-backups-table th{font-size:12px;text-transform:uppercase;color:var(--bs-secondary-color)}
        .ai-backups-status{display:inline-flex;align-items:center;gap:6px;border:1px solid var(--bs-border-color);border-radius:5px;padding:4px 7px;font-size:12px;font-weight:800}
        .ai-backups-log{max-height:460px;overflow:auto;border-radius:5px;background:#0f172a;color:#dbeafe;padding:12px;font-size:12px}
        @media(max-width:760px){.ai-backups-head{flex-direction:column}.ai-backups-actions{width:100%}.ai-backups-actions .btn,.ai-backups-actions form{width:100%}.ai-backups-actions form .btn{width:100%;justify-content:center}}
    </style>

    <div class="ai-backups-shell">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error') || $error)
            <div class="alert alert-danger">{{ session('error') ?: $error }}</div>
        @endif

        <div class="ai-backups-card">
            <div class="ai-backups-table-wrap">
                <table class="ai-backups-table">
                    <thead>
                        <tr>
                            <th>Backup</th>
                            <th>Data</th>
                            <th>Tamanho</th>
                            <th>Checksum</th>
                            <th>Manifest</th>
                            <th>Estado</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            @php
                                $filename = $backup['filename'] ?? '';
                                $size = $backup['size'] ?? null;
                            @endphp
                            <tr>
                                <td><strong>{{ $filename }}</strong></td>
                                <td>{{ $backup['created_at_from_name'] ?? $backup['created_at'] ?? '-' }}</td>
                                <td>{{ $size !== null ? number_format(((float) $size) / 1024 / 1024, 2, ',', ' ') . ' MB' : '-' }}</td>
                                <td>
                                    <span class="ai-backups-status">
                                        <i class="fa-solid {{ !empty($backup['checksum_exists']) ? 'fa-check text-success' : 'fa-triangle-exclamation text-warning' }}"></i>
                                        {{ !empty($backup['checksum_exists']) ? 'Sim' : 'Nao' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="ai-backups-status">
                                        <i class="fa-solid {{ !empty($backup['manifest_exists']) ? 'fa-check text-success' : 'fa-triangle-exclamation text-warning' }}"></i>
                                        {{ !empty($backup['manifest_exists']) ? 'Sim' : 'Nao' }}
                                    </span>
                                </td>
                                <td>{{ $backup['validation_status'] ?? 'unknown' }}</td>
                                <td>
                                    <div class="ai-backups-actions">
                                        <a href="{{ route('admin.infrastructure.ai-backups.show', $filename) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fa-solid fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('admin.infrastructure.ai-backups.download', $filename) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.infrastructure.ai-backups.checksum', $filename) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.infrastructure.ai-backups.destroy', $filename) }}" onsubmit="return confirm('Eliminar este backup e ficheiros associados?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted text-center py-4">Sem backups listados ou API admin ainda indisponivel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
