@extends(config('database-explorer.layout', 'layouts.app'))

@section('content')
    @include('database-explorer::Includes.css')

    <div class="database-explorer-shell">
        @include('database-explorer::Includes._components.header', [
            'title' => 'Database Health',
            'subtitle' => 'Consolidated structural and maintenance findings from metadata and PostgreSQL statistics.',
        ])

        @include('database-explorer::Includes._components.overview-cards', ['overview' => $overview, 'formatBytes' => $formatBytes])

        <div class="dbx-toolbar">
            <form method="GET" action="{{ route('database_explorer.health') }}" class="dbx-toolbar-form">
                <div class="dbx-form-row">
                    <label class="dbx-label">Schema</label>
                    <select name="schema" class="dbx-select">
                        <option value="">All allowed schemas</option>
                        @foreach($schemas as $schema)
                            <option value="{{ $schema['schemaName'] }}" {{ ($filters['schemaName'] ?? null) === $schema['schemaName'] ? 'selected' : '' }}>{{ $schema['schemaName'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dbx-form-row">
                    <label class="dbx-label">Table search</label>
                    <input type="text" name="q" value="{{ $filters['search'] ?? '' }}" class="dbx-input" placeholder="Table name">
                </div>
                <div class="dbx-form-row">
                    <label class="dbx-label">Severity</label>
                    <select name="severity" class="dbx-select">
                        <option value="">Any severity</option>
                        @foreach(['critical', 'warning', 'info'] as $severity)
                            <option value="{{ $severity }}" {{ ($filters['severity'] ?? null) === $severity ? 'selected' : '' }}>{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="lsg-action-btn lsg-action-btn--primary"><i class="fa-solid fa-filter"></i> Filter</button>
                <a href="{{ route('database_explorer.health') }}" class="lsg-action-btn lsg-action-btn--compact"><i class="fa-solid fa-rotate-left"></i> Reset</a>
            </form>
        </div>

        @include('database-explorer::Includes._components.findings-table', ['findings' => $findings])
    </div>

    @include('database-explorer::Includes.js')
@endsection
