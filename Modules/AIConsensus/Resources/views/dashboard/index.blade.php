@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    @include('ai-consensus::Includes.css')

    <div class="ai-workspace">
        @include('ai-consensus::Includes._components.sidebar')

        <div class="ai-content-panel">
            <div class="ai-content-card">
                <div class="table-responsive">
                    <table class="table table-striped table-hover lsg-datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Source</th>
                                <th>Template</th>
                                <th>Output</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentRuns as $run)
                                <tr>
                                    <td>#{{ $run->id }}</td>
                                    <td>{{ $run->source_module }} / {{ $run->source_type }}</td>
                                    <td>{{ $run->template?->template_key ?? '-' }}</td>
                                    <td>{{ $run->output_type }}</td>
                                    <td>@include('ai-consensus::partials.status-badge', ['status' => $run->status])</td>
                                    <td>{{ optional($run->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('ai_consensus.runs.show', $run) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="View">
                                            <span class="lsg-action-btn__icon"><i class="fas fa-eye"></i></span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
