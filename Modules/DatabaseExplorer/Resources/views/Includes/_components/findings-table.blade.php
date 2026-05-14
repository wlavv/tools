<div class="database-explorer-card databaseExplorer-card">
    <div class="database-explorer-table-wrap">
        <table class="database-explorer-table lsg-datatable">
            <thead>
                <tr>
                    <th>Severity</th>
                    <th>Code</th>
                    <th>Object</th>
                    <th>Message</th>
                    <th>Recommendation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($findings as $finding)
                    <tr>
                        <td><span class="dbx-badge dbx-badge--{{ $finding['severity'] ?? 'info' }}">{{ ucfirst($finding['severity'] ?? 'info') }}</span></td>
                        <td><code>{{ $finding['code'] ?? 'UNKNOWN' }}</code></td>
                        <td>
                            @if(!empty($finding['schemaName']) || !empty($finding['tableName']))
                                <code>{{ $finding['schemaName'] ?? '' }}{{ !empty($finding['tableName']) ? '.' . $finding['tableName'] : '' }}</code>
                            @else
                                <span class="dbx-muted">Database</span>
                            @endif
                        </td>
                        <td>{{ $finding['message'] ?? '' }}</td>
                        <td>{{ $finding['recommendation'] ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No findings for the current filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
