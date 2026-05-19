@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')
<div class="card idealab-card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Key</th><th>Name</th><th>Entrypoint</th><th>Chat</th><th>Active</th><th></th></tr></thead>
            <tbody>
                @foreach($templates as $template)
                    <tr>
                        <td><code>{{ $template->key }}</code></td>
                        <td>{{ $template->name }}</td>
                        <td>{{ $template->entrypoint_type }}</td>
                        <td>{{ $template->supports_chat ? 'Yes' : 'No' }}</td>
                        <td>{{ $template->is_active ? 'Yes' : 'No' }}</td>
                        <td class="text-end"><a href="{{ route('idealab.templates.edit', $template) }}" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pencil"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
