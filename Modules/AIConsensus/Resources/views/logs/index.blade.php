@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Created</th>
                        <th>Run</th>
                        <th>Level</th>
                        <th>Event</th>
                        <th>Message</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ optional($log->created_at)->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @if($log->run)
                                    <a href="{{ route('ai_consensus.runs.show', $log->run) }}">#{{ $log->run_id }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $log->level }}</td>
                            <td><code>{{ $log->event }}</code></td>
                            <td>{{ $log->message }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
