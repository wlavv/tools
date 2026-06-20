@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-card">
        <div class="dms-card__head">
            <div>
                <span class="dms-eyebrow">Expense Manager</span>
                <h3>Rascunho de despesa</h3>
                <span class="dms-muted">Documento associado: {{ $document->title }}</span>
            </div>
            <a href="{{ route('document-manager.documents.ai.results', $document->id) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-eye"></i> Voltar ao resultado AI
            </a>
        </div>

        @if($expenseRouteMissing ?? false)
            <div class="dms-alert dms-alert--warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                Nao encontrei uma rota ativa do Expense Manager nesta workspace. A sugestao fica abaixo para mapear quando o modulo estiver disponivel.
            </div>
        @endif
    </div>

    <div class="dms-card">
        <div class="dms-grid dms-grid--2">
            @foreach($prefill as $field => $value)
                <div class="dms-field">
                    <label>{{ str_replace('_', ' ', ucfirst($field)) }}</label>
                    <input type="text" value="{{ is_scalar($value) ? $value : json_encode($value) }}" readonly>
                </div>
            @endforeach
        </div>
    </div>
@endsection
