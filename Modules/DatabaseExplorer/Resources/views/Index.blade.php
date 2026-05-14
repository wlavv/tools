@extends(config('database-explorer.layout', 'layouts.app'))

@section('content')
    @include('database-explorer::Includes.css')

    <div class="database-explorer-shell">
        @include('database-explorer::Includes._components.header', [
            'title' => 'Database Explorer',
            'subtitle' => 'Metadata-only overview of database structure, table statistics and technical health. No application table data is queried or displayed.',
        ])

        @if(session('success'))
            <div class="dbx-alert">{{ session('success') }}</div>
        @endif

        <div class="dbx-note">
            <strong>Metadata-only mode:</strong> this module reads PostgreSQL catalogs and statistics only. It does not provide SQL console, row preview, data export or arbitrary SELECT execution.
        </div>

        @include('database-explorer::Includes._components.overview-cards', ['overview' => $overview, 'formatBytes' => $formatBytes])
        @include('database-explorer::Includes._components.filters', ['schemas' => $schemas, 'filters' => $filters])
        @include('database-explorer::Includes._components.tables-table', ['tables' => $tables, 'formatBytes' => $formatBytes])
    </div>

    @include('database-explorer::Includes.js')
@endsection
