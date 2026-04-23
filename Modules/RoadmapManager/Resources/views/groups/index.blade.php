@extends('layouts.app')

@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-panel">
    <div class="rm-table-wrap">
        <table class="rm-table">
            <thead><tr><th>Name</th><th>Slug</th><th>Status</th><th>Projects</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse($groups as $group)
                <tr>
                    <td><span class="badge me-2" style="background:{{ $group->color }}">&nbsp;</span>{{ $group->name }}</td>
                    <td>{{ $group->slug }}</td>
                    <td>{{ $group->status }}</td>
                    <td>{{ $group->projects_count }}</td>
                    <td class="text-end">
                        <div class="rm-table-actions">
                            <a href="{{ route('roadmap_manager.groups.show', $group->id) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-eye"></i></a>
                            <a href="{{ route('roadmap_manager.groups.edit', $group->id) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-pencil"></i></a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="rm-muted">No groups found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $groups->links() }}</div>
@endsection
