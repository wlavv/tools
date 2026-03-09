@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div class="container-fluid ai-consensus-page py-3">
    @include('ai-consensus::Includes.css')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @include('ai-consensus::Includes._components.header')
    @include('ai-consensus::Includes._components.stats')
    @include('ai-consensus::Includes._components.filters')
    @include('ai-consensus::Includes._components.cards')
    @include('ai-consensus::Includes._components.table')
    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
