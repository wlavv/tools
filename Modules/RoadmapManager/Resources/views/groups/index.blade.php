@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Roadmap Groups</h1>
    <a href="{{ route('roadmap.groups.create') }}" class="btn btn-outline-success"><i class="fa-solid fa-plus"></i> New</a>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Projects</th><th></th></tr></thead>
            <tbody>
            @forelse($groups as $group)
                <tr>
                    <td><span class="badge me-2" style="background:{{ $group->color }}">&nbsp;</span>{{ $group->name }}</td>
                    <td>{{ $group->slug }}</td>
                    <td>{{ $group->status }}</td>
                    <td>{{ $group->projects_count }}</td>
                    <td class="text-end">
                        <a href="{{ route('roadmap.groups.show', $group->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('roadmap.groups.edit', $group->id) }}" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-muted">No groups found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $groups->links() }}</div>
@endsection
