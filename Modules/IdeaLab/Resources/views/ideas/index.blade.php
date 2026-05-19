@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')

<div class="card idealab-card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle" id="idealab-table">
            <thead>
                <tr>
                    <th>Idea</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Score</th>
                    <th>Readiness</th>
                    <th>Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ideas as $idea)
                    <tr>
                        <td>
                            <strong>{{ $idea->title }}</strong><br>
                            <small class="text-muted">{{ str($idea->description_raw)->limit(100) }}</small>
                        </td>
                        <td>{{ $idea->category?->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary idealab-badge">{{ config('idealab.statuses.' . $idea->status, $idea->status) }}</span></td>
                        <td><span class="badge bg-info idealab-badge">{{ config('idealab.priorities.' . $idea->priority, $idea->priority) }}</span></td>
                        <td><strong>{{ $idea->final_score ?? '—' }}</strong></td>
                        <td>{{ $idea->readiness_label }}</td>
                        <td>{{ $idea->updated_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('idealab.show', $idea) }}" class="btn btn-sm btn-outline-primary" title="Show"><i class="fa-solid fa-eye"></i></a>
                            <a href="{{ route('idealab.edit', $idea) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fa-solid fa-pencil"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $ideas->links() }}
    </div>
</div>
@endsection
