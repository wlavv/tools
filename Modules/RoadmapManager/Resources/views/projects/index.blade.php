@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Projects</h1>
    <a href="{{ route('roadmap.projects.create') }}" class="btn btn-outline-success"><i class="fa-solid fa-plus"></i> New</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Status</th><th>Priority</th><th>Groups</th><th>Deadline</th><th></th></tr></thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->status }}</td>
                    <td>{{ $project->priority }}</td>
                    <td>
                        @foreach($project->roadmapGroups as $group)
                            <span class="badge" style="background:{{ $group->color }}">{{ $group->name }}</span>
                        @endforeach
                    </td>
                    <td>{{ $project->deadline }}</td>
                    <td class="text-end">
                        <a href="{{ route('roadmap.projects.show', $project->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('roadmap.projects.edit', $project->id) }}" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">No projects found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $projects->links() }}</div>
@endsection
