@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Driver</th>
                        <th>Model</th>
                        <th>Priority</th>
                        <th>Weight</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                        <tr>
                            <form method="POST" action="{{ route('ai_consensus.providers.update', $provider) }}">
                                @csrf
                                @method('PATCH')
                                <td>
                                    <code>{{ $provider->provider_key }}</code>
                                    <input name="name" class="form-control form-control-sm mt-1" value="{{ $provider->name }}" required>
                                </td>
                                <td>{{ $provider->driver }}</td>
                                <td><input name="model" class="form-control form-control-sm" value="{{ $provider->model }}"></td>
                                <td><input name="priority" type="number" class="form-control form-control-sm" value="{{ $provider->priority }}" required></td>
                                <td><input name="weight" type="number" step="0.01" class="form-control form-control-sm" value="{{ $provider->weight }}" required></td>
                                <td>
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($provider->is_active)>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-save"></i>
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
