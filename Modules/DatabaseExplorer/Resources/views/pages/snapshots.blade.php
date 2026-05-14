@extends(config('database-explorer.layout', 'layouts.app'))

@section('content')
    @include('database-explorer::Includes.css')

    <div class="database-explorer-shell">
        @include('database-explorer::Includes._components.header', [
            'title' => 'Database Snapshots',
            'subtitle' => 'Historical metadata snapshots used to monitor growth and health evolution over time.',
        ])

        @if(session('success'))
            <div class="dbx-alert">{{ session('success') }}</div>
        @endif

        <div class="dbx-toolbar">
            <div class="dbx-page-actions" style="justify-content:flex-start">
                <form method="POST" action="{{ route('database_explorer.snapshots.collect') }}" class="lsg-action-form">
                    @csrf
                    <button type="submit" class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-camera"></i> Collect snapshot</button>
                </form>
                <a href="{{ route('database_explorer.snapshots') }}" class="lsg-action-btn lsg-action-btn--compact"><i class="fa-solid fa-rotate"></i> Refresh</a>
            </div>
        </div>

        <div class="database-explorer-card databaseExplorer-card">
            <div class="database-explorer-table-wrap">
                <table class="database-explorer-table lsg-datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Collected at</th>
                            <th>Database</th>
                            <th>Tables</th>
                            <th>Views</th>
                            <th>Indexes</th>
                            <th>Estimated rows</th>
                            <th>Total size</th>
                            <th>Health</th>
                            <th>Findings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($snapshots as $snapshot)
                            <tr>
                                <td>#{{ $snapshot['id'] }}</td>
                                <td>{{ $snapshot['createdAt'] ?? '—' }}</td>
                                <td>{{ $snapshot['databaseName'] ?? '—' }}</td>
                                <td>{{ number_format((int) ($snapshot['tableCount'] ?? 0)) }}</td>
                                <td>{{ number_format((int) ($snapshot['viewCount'] ?? 0)) }}</td>
                                <td>{{ number_format((int) ($snapshot['indexCount'] ?? 0)) }}</td>
                                <td>{{ number_format((int) ($snapshot['estimatedRows'] ?? 0)) }}</td>
                                <td>{{ $formatBytes((int) ($snapshot['totalSizeBytes'] ?? 0)) }}</td>
                                <td>@include('database-explorer::Includes._components.health-badge', ['status' => $snapshot['healthStatus'], 'score' => $snapshot['healthScore']])</td>
                                <td>{{ number_format((int) ($snapshot['findingsCount'] ?? 0)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10">No snapshots collected yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('database-explorer::Includes.js')
@endsection
