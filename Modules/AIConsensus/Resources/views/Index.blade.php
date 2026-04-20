@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div>
    @include('ai-consensus::Includes.css')
    @if(session('success')) <div class="ai-alert ai-alert--success" style="margin-bottom: 15px;">{{ session('success') }}</div> @endif
    @if($errors->any())
        <div class="ai-alert ai-alert--error">
            <strong>Existem erros no formulário.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    @include('ai-consensus::Includes._components.stats')
    <div id="showCards" style="display: none;margin-bottom: 20px;">
        @include('ai-consensus::Includes._components.cards')
    </div>
    @include('ai-consensus::Includes._components.table')
    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
