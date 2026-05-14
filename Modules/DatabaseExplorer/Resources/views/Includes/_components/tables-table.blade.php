<div class="database-explorer-card databaseExplorer-card">
    <div class="database-explorer-table-wrap">
        <table class="database-explorer-table lsg-datatable">
            <thead>
                <tr>
                    <th>Table</th>
                    <th>Type</th>
                    <th>Rows est.</th>
                    <th>Total size</th>
                    <th>Data / Index</th>
                    <th>Columns</th>
                    <th>Indexes</th>
                    <th>PK</th>
                    <th>Last analyzed</th>
                    <th>Health</th>
                    <th class="text-center" style="width: 95px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tables as $dbTable)
                    <tr>
                        <td>
                            <div class="dbx-table-title">
                                <strong>{{ $dbTable['tableName'] }}</strong>
                                <span>{{ $dbTable['schemaName'] }}</span>
                            </div>
                        </td>
                        <td><span class="dbx-badge dbx-badge--info">{{ $dbTable['tableType'] }}</span></td>
                        <td class="dbx-nowrap">{{ number_format((int) ($dbTable['estimatedRows'] ?? 0)) }}</td>
                        <td class="dbx-nowrap">{{ $formatBytes((int) ($dbTable['totalSizeBytes'] ?? 0)) }}</td>
                        <td class="dbx-nowrap">
                            {{ $formatBytes((int) ($dbTable['dataSizeBytes'] ?? 0)) }} / {{ $formatBytes((int) ($dbTable['indexSizeBytes'] ?? 0)) }}
                        </td>
                        <td>{{ number_format((int) ($dbTable['columnCount'] ?? 0)) }}</td>
                        <td>{{ number_format((int) ($dbTable['indexCount'] ?? 0)) }}</td>
                        <td>{!! $dbTable['hasPrimaryKey'] ? '<span class="dbx-badge dbx-badge--healthy">Yes</span>' : '<span class="dbx-badge dbx-badge--critical">No</span>' !!}</td>
                        <td class="dbx-nowrap">{{ $dbTable['lastAnalyzedAt'] ? \Illuminate\Support\Carbon::parse($dbTable['lastAnalyzedAt'])->format('d/m/Y H:i') : '—' }}</td>
                        <td>@include('database-explorer::Includes._components.health-meter', ['score' => $dbTable['healthScore'], 'status' => $dbTable['healthStatus']])</td>
                        <td>
                            <div class="dbx-actions dbx-actions--center">
                                <a href="{{ route('database_explorer.show', [$dbTable['schemaName'], $dbTable['tableName']]) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="Details"><i class="fa-solid fa-magnifying-glass-chart"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11">{{ config('database-explorer.ui.empty_state.text', 'No tables found.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="dbx-mobile-list">
        @forelse($tables as $dbTable)
            <div class="dbx-mobile-item databaseExplorer-card">
                <div class="dbx-mobile-item__header">
                    <div>
                        <strong>{{ $dbTable['schemaName'] }}.{{ $dbTable['tableName'] }}</strong>
                        <div class="dbx-mobile-item__sub">{{ $dbTable['tableType'] }}</div>
                    </div>
                    <a href="{{ route('database_explorer.show', [$dbTable['schemaName'], $dbTable['tableName']]) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-magnifying-glass-chart"></i></a>
                </div>
                <div class="dbx-mobile-grid">
                    <div class="dbx-mobile-metric"><span>Rows est.</span><strong>{{ number_format((int) ($dbTable['estimatedRows'] ?? 0)) }}</strong></div>
                    <div class="dbx-mobile-metric"><span>Total size</span><strong>{{ $formatBytes((int) ($dbTable['totalSizeBytes'] ?? 0)) }}</strong></div>
                    <div class="dbx-mobile-metric"><span>Indexes</span><strong>{{ number_format((int) ($dbTable['indexCount'] ?? 0)) }}</strong></div>
                    <div class="dbx-mobile-metric"><span>Health</span><strong>{{ ucfirst($dbTable['healthStatus']) }} · {{ $dbTable['healthScore'] }}</strong></div>
                </div>
            </div>
        @empty
            <div class="dbx-mobile-item databaseExplorer-card">{{ config('database-explorer.ui.empty_state.text', 'No tables found.') }}</div>
        @endforelse
    </div>
</div>
