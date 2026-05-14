@extends(config('database-explorer.layout', 'layouts.app'))

@section('content')
    @include('database-explorer::Includes.css')

    <div class="database-explorer-shell">
        @include('database-explorer::Includes._components.header', [
            'title' => $table['schemaName'] . '.' . $table['tableName'],
            'subtitle' => 'Table structure, fields, indexes, constraints, relationships and technical health. No row data is displayed.',
        ])

        <div class="dbx-grid-3">
            <div class="dbx-meta"><strong>Type</strong><div>{{ $table['tableType'] ?? '—' }}</div></div>
            <div class="dbx-meta"><strong>Estimated rows</strong><div>{{ number_format((int) ($table['estimatedRows'] ?? 0)) }}</div></div>
            <div class="dbx-meta"><strong>Total size</strong><div>{{ $formatBytes((int) ($table['totalSizeBytes'] ?? 0)) }}</div></div>
            <div class="dbx-meta"><strong>Data size</strong><div>{{ $formatBytes((int) ($table['dataSizeBytes'] ?? 0)) }}</div></div>
            <div class="dbx-meta"><strong>Index size</strong><div>{{ $formatBytes((int) ($table['indexSizeBytes'] ?? 0)) }}</div></div>
            <div class="dbx-meta"><strong>Health</strong><div>@include('database-explorer::Includes._components.health-badge', ['status' => $table['healthStatus'], 'score' => $table['healthScore']])</div></div>
        </div>

        <div class="database-explorer-card databaseExplorer-card" data-dbx-tabs-root>
            <div class="dbx-tabs">
                <button type="button" class="dbx-tab is-active" data-dbx-tab="columns">Columns</button>
                <button type="button" class="dbx-tab" data-dbx-tab="indexes">Indexes</button>
                <button type="button" class="dbx-tab" data-dbx-tab="constraints">Constraints</button>
                <button type="button" class="dbx-tab" data-dbx-tab="relationships">Relationships</button>
                <button type="button" class="dbx-tab" data-dbx-tab="health">Health</button>
            </div>

            <div class="dbx-tab-panel is-active" data-dbx-panel="columns">
                <h2 class="dbx-section-title">Columns</h2>
                <div class="database-explorer-table-wrap">
                    <table class="database-explorer-table lsg-datatable">
                        <thead><tr><th>#</th><th>Name</th><th>Type</th><th>Nullable</th><th>Default</th><th>PK</th><th>FK</th><th>Indexed</th><th>Reference</th><th>Comment</th></tr></thead>
                        <tbody>
                            @foreach($table['columns'] as $column)
                                <tr>
                                    <td>{{ $column['ordinalPosition'] }}</td>
                                    <td><code>{{ $column['name'] }}</code></td>
                                    <td>{{ $column['fullDataType'] ?? $column['dataType'] }}</td>
                                    <td>{{ $column['isNullable'] ? 'Yes' : 'No' }}</td>
                                    <td class="dbx-definition">{{ $column['defaultValue'] ?? '—' }}</td>
                                    <td>{{ $column['isPrimaryKey'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ $column['isForeignKey'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ $column['isIndexed'] ? 'Yes' : 'No' }}</td>
                                    <td>
                                        @if($column['referencedTable'])
                                            <code>{{ $column['referencedSchema'] }}.{{ $column['referencedTable'] }}.{{ $column['referencedColumn'] }}</code>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $column['comment'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dbx-tab-panel" data-dbx-panel="indexes">
                <h2 class="dbx-section-title">Indexes</h2>
                <div class="database-explorer-table-wrap">
                    <table class="database-explorer-table lsg-datatable">
                        <thead><tr><th>Name</th><th>Columns</th><th>Type</th><th>Unique</th><th>Primary</th><th>Valid</th><th>Size</th><th>Scans</th><th>Health</th><th>Definition</th></tr></thead>
                        <tbody>
                            @forelse($table['indexes'] as $index)
                                <tr>
                                    <td><code>{{ $index['indexName'] }}</code></td>
                                    <td>{{ implode(', ', $index['columns'] ?? []) ?: 'Expression index' }}</td>
                                    <td>{{ $index['indexType'] ?? '—' }}</td>
                                    <td>{{ $index['isUnique'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ $index['isPrimary'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ $index['isValid'] ? 'Yes' : 'No' }}</td>
                                    <td>{{ $formatBytes((int) ($index['sizeBytes'] ?? 0)) }}</td>
                                    <td>{{ number_format((int) ($index['scans'] ?? 0)) }}</td>
                                    <td>@include('database-explorer::Includes._components.health-badge', ['status' => $index['healthStatus'] ?? 'healthy', 'score' => $index['healthScore'] ?? 100])</td>
                                    <td><div class="dbx-definition" title="{{ $index['definition'] ?? '' }}">{{ $index['definition'] ?? '—' }}</div></td>
                                </tr>
                            @empty
                                <tr><td colspan="10">No indexes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dbx-tab-panel" data-dbx-panel="constraints">
                <h2 class="dbx-section-title">Constraints</h2>
                <div class="database-explorer-table-wrap">
                    <table class="database-explorer-table lsg-datatable">
                        <thead><tr><th>Name</th><th>Type</th><th>Columns</th><th>Validated</th><th>Definition</th></tr></thead>
                        <tbody>
                            @forelse($table['constraints'] as $constraint)
                                <tr>
                                    <td><code>{{ $constraint['constraintName'] }}</code></td>
                                    <td><span class="dbx-badge dbx-badge--info">{{ $constraint['constraintType'] }}</span></td>
                                    <td>{{ implode(', ', $constraint['columns'] ?? []) ?: '—' }}</td>
                                    <td>{{ $constraint['isValidated'] ? 'Yes' : 'No' }}</td>
                                    <td><div class="dbx-definition" title="{{ $constraint['definition'] ?? '' }}">{{ $constraint['definition'] ?? '—' }}</div></td>
                                </tr>
                            @empty
                                <tr><td colspan="5">No constraints found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dbx-tab-panel" data-dbx-panel="relationships">
                <h2 class="dbx-section-title">Relationships</h2>
                <div class="database-explorer-table-wrap">
                    <table class="database-explorer-table lsg-datatable">
                        <thead><tr><th>Direction</th><th>Source</th><th>Source columns</th><th>Target</th><th>Target columns</th><th>Constraint</th></tr></thead>
                        <tbody>
                            @forelse($table['relationships'] as $relationship)
                                <tr>
                                    <td><span class="dbx-badge dbx-badge--info">{{ ucfirst($relationship['direction']) }}</span></td>
                                    <td><code>{{ $relationship['sourceSchema'] }}.{{ $relationship['sourceTable'] }}</code></td>
                                    <td>{{ implode(', ', $relationship['sourceColumns'] ?? []) }}</td>
                                    <td><code>{{ $relationship['targetSchema'] }}.{{ $relationship['targetTable'] }}</code></td>
                                    <td>{{ implode(', ', $relationship['targetColumns'] ?? []) }}</td>
                                    <td><code>{{ $relationship['constraintName'] }}</code></td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No foreign-key relationships found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dbx-tab-panel" data-dbx-panel="health">
                <h2 class="dbx-section-title">Health findings</h2>
                @include('database-explorer::Includes._components.findings-table', ['findings' => $table['findings'] ?? []])
            </div>
        </div>
    </div>

    @include('database-explorer::Includes.js')
@endsection
