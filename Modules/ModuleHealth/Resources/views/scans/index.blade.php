@extends('module-health::layouts.module')

@section('content')
@include('module-health::partials.styles')

<div class="mh-shell">
    <div class="mh-card mh-panel">
        <div class="mh-card-head">
            <div>
                <h5 class="mh-title">Scan History</h5>
                <div class="mh-subtitle">Previous structural checks for registered modules.</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table mh-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Modules</th>
                        <th>Broken</th>
                        <th>Incomplete</th>
                        <th>Functional</th>
                        <th>Enhanced</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($scans as $scan)
                        <tr>
                            <td class="mh-module-name">{{ $scan->id }}</td>
                            <td>{{ $scan->finished_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ $scan->modules_total }}</td>
                            <td>{{ $scan->broken_total }}</td>
                            <td>{{ $scan->incomplete_total }}</td>
                            <td>{{ $scan->functional_total }}</td>
                            <td>{{ $scan->enhanced_total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="mh-empty">No scan history available.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $scans->links() }}
        </div>
    </div>
</div>
@endsection
