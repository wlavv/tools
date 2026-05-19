@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    <div class="d-flex justify-content-end gap-2 mb-3">
        <a href="{{ route('ai_consensus.runs.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-stream"></i> Runs
        </a>
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

    <div class="row g-3 mb-3">
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Runs</div><h4 class="mb-0">{{ $stats['runs'] }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Open</div><h4 class="mb-0">{{ $stats['pending'] }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Templates</div><h4 class="mb-0">{{ $stats['templates'] }}</h4></div></div></div>
        <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted small">Active Providers</div><h4 class="mb-0">{{ $stats['providers'] }}</h4></div></div></div>
    </div>

    <div class="card">
        <div class="card-header">Recent Runs</div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Source</th>
                        <th>Template</th>
                        <th>Output</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentRuns as $run)
                        <tr>
                            <td>#{{ $run->id }}</td>
                            <td>{{ $run->source_module }} / {{ $run->source_type }}</td>
                            <td>{{ $run->template?->template_key ?? '-' }}</td>
                            <td>{{ $run->output_type }}</td>
                            <td>@include('ai-consensus::partials.status-badge', ['status' => $run->status])</td>
                            <td>{{ optional($run->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('ai_consensus.runs.show', $run) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
