@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Milestones</h1>
    <a href="{{ route('roadmap.milestones.create') }}" class="btn btn-outline-success"><i class="fa-solid fa-plus"></i> New</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Project</th><th>Status</th><th>End</th><th></th></tr></thead>
            <tbody>
            @forelse($milestones as $milestone)
                <tr>
                    <td>{{ $milestone->name }}</td>
                    <td>{{ $milestone->project->name ?? '-' }}</td>
                    <td>{{ $milestone->status }}</td>
                    <td>{{ optional($milestone->planned_end_date)->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('roadmap.milestones.show', $milestone->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('roadmap.milestones.edit', $milestone->id) }}" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">No milestones found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $milestones->links() }}</div>
@endsection
