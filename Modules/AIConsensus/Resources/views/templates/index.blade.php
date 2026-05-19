@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover datatable">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Name</th>
                        <th>Scope</th>
                        <th>Category</th>
                        <th>Output</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td><code>{{ $template->template_key }}</code></td>
                            <td>{{ $template->name }}</td>
                            <td>{{ $template->module_scope }}</td>
                            <td>{{ $template->category }}</td>
                            <td>{{ $template->default_output_type }}</td>
                            <td>{{ $template->is_active ? 'Yes' : 'No' }}</td>
                            <td class="text-end">
                                <a href="{{ route('ai_consensus.templates.edit', $template) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $templates->links() }}
        </div>
    </div>
</div>
@endsection
