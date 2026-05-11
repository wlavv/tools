@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-card">
        <table class="dms-table document-lsg-datatable">
            <thead><tr><th>Pasta</th><th>Workspace</th><th>Path</th><th>Depth</th><th></th></tr></thead>
            <tbody>
                @forelse($folders as $folder)
                    <tr>
                        <td><strong>{{ $folder->name }}</strong><span class="dms-muted">{{ $folder->slug }}</span></td>
                        <td>{{ $folder->workspace_name ?: '-' }}</td>
                        <td>{{ $folder->path ?: '-' }}</td>
                        <td>{{ $folder->depth }}</td>
                        <td class="text-right">
                            <a href="{{ route('document-manager.folders.edit', $folder->id) }}" class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-pencil"></i></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">Sem folders.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
