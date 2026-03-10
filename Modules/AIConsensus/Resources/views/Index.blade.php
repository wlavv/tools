@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div class="ai-consensus-page">
    @include('ai-consensus::Includes.css')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @include('ai-consensus::Includes._components.stats')
    <div id="showCards" style="display: none;margin-bottom: 20px;">
        @include('ai-consensus::Includes._components.cards')
    </div>
    {{--
    @include('ai-consensus::Includes._components.filters')
    --}}
    @include('ai-consensus::Includes._components.table')
    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
