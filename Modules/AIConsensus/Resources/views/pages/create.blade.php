@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div class="ai-consensus-page">
    @include('ai-consensus::Includes.css')
    @if($errors->any())
        <div class="alert alert-danger">
            <div><strong>Existem erros no formulário.</strong></div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @include('ai-consensus::Includes._components.form', [ 'action' => route('ai_consensus.store'), 'method' => 'POST', 'submitLabel' => 'Criar e processar' ])
    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
