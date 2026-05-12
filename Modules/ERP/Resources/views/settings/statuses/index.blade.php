@extends('erp::layouts.module')

@section('erp-content')
<div class="lsg-card erp-panel">
    <div class="erp-table-shell">
        <table class="table erp-table align-middle mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Record</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name ?? $item->code ?? $item->key ?? 'Record #' . $item->id }}</td>
                        <td>{{ optional($item->created_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
