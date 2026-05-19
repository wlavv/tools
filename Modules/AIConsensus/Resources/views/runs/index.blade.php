@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('ai_consensus.templates.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-layer-group"></i> Templates
        </a>
        <a href="{{ route('ai_consensus.providers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-plug"></i> Providers
        </a>
        <a href="{{ route('ai_consensus.runs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Run
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Source</th>
                            <th>Template</th>
                            <th>Output</th>
                            <th>Status</th>
                            <th>Score</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($runs as $run)
                            <tr>
                                <td>#{{ $run->id }}</td>
                                <td>{{ $run->source_module }} / {{ $run->source_type }}</td>
                                <td>{{ $run->template?->template_key ?? '-' }}</td>
                                <td>{{ $run->output_type }}</td>
                                <td>@include('ai-consensus::partials.status-badge', ['status' => $run->status])</td>
                                <td>{{ $run->final_score ?? '-' }}</td>
                                <td>{{ optional($run->created_at)->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    @if(in_array($run->status, ['pending', 'failed'], true))
                                        <form method="POST" action="{{ route('ai_consensus.runs.process', $run) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-success" title="Process">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('ai_consensus.runs.show', $run) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $runs->links() }}
        </div>
    </div>
</div>
@endsection
