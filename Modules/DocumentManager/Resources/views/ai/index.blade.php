@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-counter-line dms-counter-line--ai">
        <div class="dms-panel dms-panel--warning">
            <div class="dms-panel__icon"><i class="fa-solid fa-file-lines"></i></div>
            <div><span>OCR pending</span><strong>{{ $stats['ocr_pending'] }}</strong></div>
        </div>
        <div class="dms-panel dms-panel--primary">
            <div class="dms-panel__icon"><i class="fa-solid fa-vector-square"></i></div>
            <div><span>Embeddings</span><strong>{{ $stats['embeddings'] }}</strong></div>
        </div>
        <div class="dms-panel dms-panel--primary">
            <div class="dms-panel__icon"><i class="fa-solid fa-align-left"></i></div>
            <div><span>Summaries</span><strong>{{ $stats['summaries'] }}</strong></div>
        </div>
        <div class="dms-panel dms-panel--primary">
            <div class="dms-panel__icon"><i class="fa-solid fa-chart-simple"></i></div>
            <div><span>Analysis</span><strong>{{ $stats['analysis'] }}</strong></div>
        </div>
        <div class="dms-panel dms-panel--primary">
            <div class="dms-panel__icon"><i class="fa-solid fa-brain"></i></div>
            <div><span>AI Logs</span><strong>{{ $stats['ai_logs'] }}</strong></div>
        </div>
    </div>

    <div class="dms-grid dms-grid--2">
        <div class="dms-card">
            <h3>Providers</h3>
            <div class="dms-kv-list">
                <div><span>OCR</span><strong>{{ $ocrHealth['provider'] }} - {{ $ocrHealth['message'] }}</strong></div>
                <div><span>AI</span><strong>{{ $aiHealth['provider'] }} - {{ $aiHealth['message'] }}</strong></div>
                <div><span>Embeddings</span><strong>{{ config('documentmanager.providers.embeddings') }}</strong></div>
                <div><span>Search</span><strong>{{ config('documentmanager.providers.search') }}</strong></div>
            </div>
        </div>

        <div class="dms-card">
            <h3>AI capabilities</h3>
            <div class="dms-pipeline">
                @foreach(['summaries', 'classification', 'keywords', 'entities', 'risk analysis', 'duplicates', 'relations', 'auto tags', 'embeddings'] as $item)
                    <span>{{ $item }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <div class="dms-card">
        <h3>AI logs</h3>
        <table class="dms-table document-lsg-datatable">
            <thead><tr><th>Provider</th><th>Operation</th><th>Status</th><th>Message</th><th>Time</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->provider ?: '-' }}</td>
                        <td>{{ $log->operation }}</td>
                        <td><span class="dms-badge">{{ $log->status }}</span></td>
                        <td>{{ $log->message ?: '-' }}</td>
                        <td>{{ $log->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Sem logs AI.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
