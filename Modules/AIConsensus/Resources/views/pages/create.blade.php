@extends(config('ai_consensus.layout', 'layouts.app'))

@section('content')
<div class="container-fluid ai-consensus-page py-3">
    @include('ai-consensus::Includes.css')

    <div class="ai-card">
        <h1 class="mb-1">Novo pedido AI Consensus</h1>
        <div class="ai-muted">Cria um run, anexa ficheiros e processa Claude + Gemini + OpenAI.</div>
    </div>

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

    @include('ai-consensus::Includes._components.form', [
        'action' => route('ai_consensus.store'),
        'method' => 'POST',
        'submitLabel' => 'Criar e processar',
    ])

    @include('ai-consensus::Includes._components.modals')
</div>
@endsection

@push('scripts')
    @include('ai-consensus::Includes.js')
@endpush
