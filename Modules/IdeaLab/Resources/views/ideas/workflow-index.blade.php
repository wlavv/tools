@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')

<div class="card idealab-card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle lsg-datatable" id="idealab-workflow-table">
            <thead>
                <tr>
                    <th>Idea</th>
                    <th>Workflow</th>
                    <th>Sandbox</th>
                    <th>Compliance</th>
                    <th>Issues</th>
                    <th>Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ideas as $idea)
                    @php
                        $workflow = $snapshots[$idea->id] ?? [];
                        $sandbox = $workflow['sandbox'] ?? [];
                        $compliance = $workflow['compliance'] ?? [];
                        $issues = $workflow['issues'] ?? [];
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $idea->title }}</strong><br>
                            <small class="text-muted">{{ $idea->category?->name ?? 'Sem categoria' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border idealab-badge">{{ str_replace('_', ' ', $workflow['current'] ?? 'draft') }}</span>
                            <div class="small text-muted mt-1">{{ collect($workflow['steps'] ?? [])->where('done', true)->count() }} / {{ count($workflow['steps'] ?? []) }} steps</div>
                        </td>
                        <td>
                            <strong>{{ $sandbox['module_name'] ?? '-' }}</strong>
                            @if(!empty($sandbox['module_path']))
                                <code class="small d-block text-wrap">{{ $sandbox['module_path'] }}</code>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $compliance['final_status'] ?? '-' }}</strong>
                            <div class="small text-muted">Score {{ $compliance['final_score'] ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ count($issues) ? 'bg-warning text-dark' : 'bg-success' }}">{{ count($issues) }}</span>
                        </td>
                        <td>{{ $idea->updated_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('idealab.show', $idea) }}#tool-workflow" class="btn btn-sm btn-outline-primary" title="Open workflow">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('idealab.workflow.compliance', $idea) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary" title="Run compliance">
                                    <i class="fa-solid fa-play"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fa-solid fa-inbox me-1"></i> No ideas found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $ideas->links() }}
    </div>
</div>
@endsection
