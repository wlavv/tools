@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-grid dms-grid--3">
        <div class="dms-card dms-kpi"><span>Module</span><strong>{{ $report['module_version'] }}</strong></div>
        <div class="dms-card dms-kpi"><span>Laravel</span><strong>{{ $report['laravel_version'] }}</strong></div>
        <div class="dms-card dms-kpi"><span>PHP</span><strong>{{ $report['php_version'] }}</strong></div>
    </div>

    <div class="dms-grid dms-grid--2">
        <div class="dms-card">
            <h3>Tabelas</h3>
            <div class="dms-check-list">
                @foreach($report['tables'] as $table => $exists)
                    <div class="{{ $exists ? 'is-ok' : 'is-fail' }}">
                        <i class="fa-solid {{ $exists ? 'fa-check' : 'fa-xmark' }}"></i>
                        <span>{{ $table }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="dms-card">
            <h3>Rotas</h3>
            <div class="dms-check-list">
                @foreach($report['routes'] as $route => $exists)
                    <div class="{{ $exists ? 'is-ok' : 'is-fail' }}">
                        <i class="fa-solid {{ $exists ? 'fa-check' : 'fa-xmark' }}"></i>
                        <span>{{ $route }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="dms-grid dms-grid--2">
        <div class="dms-card">
            <h3>Storage / Providers</h3>
            <div class="dms-kv-list">
                <div><span>Storage</span><strong>{{ $report['storage']['disk'] }} / {{ $report['storage']['message'] }}</strong></div>
                <div><span>Root</span><strong>{{ $report['storage']['root'] }}</strong></div>
                <div><span>OCR</span><strong>{{ $report['ocr']['provider'] }} / {{ $report['ocr']['message'] }}</strong></div>
                <div><span>AI</span><strong>{{ $report['ai']['provider'] }} / {{ $report['ai']['message'] }}</strong></div>
                <div><span>failed_jobs</span><strong>{{ $report['has_failed_jobs_table'] ? 'ok' : 'missing' }}</strong></div>
            </div>
        </div>

        <div class="dms-card">
            <h3>Queues</h3>
            <div class="dms-kv-list">
                @foreach($report['queue_names'] as $label => $queue)
                    <div><span>{{ $label }}</span><strong>{{ $queue }}</strong></div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="dms-card">
        <h3>Log proprio</h3>
        <p class="dms-muted">{{ $report['log_path'] }}</p>
        <pre class="dms-log">{{ $report['log_tail'] }}</pre>
    </div>
@endsection
