@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-card">
        <div class="dms-card__head">
            <div>
                <span class="dms-eyebrow">LSG AI</span>
                <h3>Resultados AI / Despesa</h3>
                <span class="dms-muted">{{ $document->title }}</span>
            </div>
            <div class="dms-actions">
                <form method="POST" action="{{ route('document-manager.documents.ai.extract-expense', $document->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> Processar AI / Despesa
                    </button>
                </form>
                <a href="{{ route('document-manager.documents.show', $document->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-file-lines"></i> Documento
                </a>
            </div>
        </div>
    </div>

    @forelse($results as $result)
        @php
            $payload = is_string($result->extracted_payload ?? null)
                ? json_decode($result->extracted_payload, true)
                : (array) ($result->extracted_payload ?? []);
            $expense = $payload['expense'] ?? [];
            $confidence = $expense['confidence'] ?? null;
        @endphp

        <div class="dms-card">
            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">{{ $result->service ?: 'documents.extract_expense' }}</span>
                    <h3>{{ ucfirst(str_replace('_', ' ', $result->operation)) }}</h3>
                    <span class="dms-muted">{{ $result->processed_at ?: $result->created_at }}</span>
                </div>
                <div class="dms-chip-row">
                    <span class="{{ $result->status === 'failed' ? 'dms-badge--soft' : '' }}">
                        <i class="fa-solid {{ $result->status === 'failed' ? 'fa-triangle-exclamation' : 'fa-check' }}"></i>
                        {{ $result->status }}
                    </span>
                    <span class="dms-badge dms-badge--soft">{{ $result->model ?: 'model n/a' }}</span>
                    @if($confidence !== null)
                        <span class="dms-badge dms-badge--soft">{{ number_format((float) $confidence * 100, 0) }}% confiança</span>
                    @endif
                </div>
            </div>

            @if($result->error_message)
                <div class="dms-alert dms-alert--danger">
                    <i class="fa-solid fa-triangle-exclamation"></i> {{ $result->error_message }}
                </div>
            @endif

            <div class="dms-grid dms-grid--2">
                <div>
                    <h3>Despesa sugerida</h3>
                    <div class="dms-kv-list mt-3">
                        <div><span>Fornecedor</span><strong>{{ $expense['supplier_name'] ?? '-' }}</strong></div>
                        <div><span>NIF</span><strong>{{ $expense['supplier_vat'] ?? '-' }}</strong></div>
                        <div><span>Fatura</span><strong>{{ $expense['invoice_number'] ?? '-' }}</strong></div>
                        <div><span>Data</span><strong>{{ $expense['invoice_date'] ?? '-' }}</strong></div>
                        <div><span>Total</span><strong>{{ $expense['total'] ?? '-' }} {{ $expense['currency'] ?? 'EUR' }}</strong></div>
                        <div><span>IVA</span><strong>{{ $expense['tax_amount'] ?? '-' }}</strong></div>
                        <div><span>Categoria</span><strong>{{ $expense['category_suggestion'] ?? '-' }}</strong></div>
                        <div><span>Notas</span><strong>{{ $expense['notes'] ?? '-' }}</strong></div>
                    </div>

                    @if(($result->status ?? '') !== 'failed')
                        <div class="dms-actions mt-3">
                            <a href="{{ route('document-manager.documents.ai.create-expense', [$document->id, $result->id]) }}" class="btn btn-outline-success btn-sm">
                                <i class="fa-solid fa-receipt"></i> Criar despesa a partir da sugestao
                            </a>
                        </div>
                    @endif
                </div>

                <div>
                    <h3>OCR extraido</h3>
                    <div class="dms-grid dms-grid--3 mt-3">
                        <div class="dms-kv-tile"><span>Texto</span><strong>{{ $result->text_length ?? 0 }}</strong></div>
                        <div class="dms-kv-tile"><span>Tempo</span><strong>{{ $result->processing_time_ms ?: '-' }} ms</strong></div>
                        <div class="dms-kv-tile"><span>Idioma</span><strong>{{ $result->language ?: '-' }}</strong></div>
                    </div>
                    <div class="dms-note mt-3">
                        <p>{{ $result->text ?: 'Sem texto OCR.' }}</p>
                    </div>
                </div>
            </div>

            <details class="dms-card dms-collapsible-panel mt-3">
                <summary>
                    <span><i class="fa-solid fa-code"></i> Payload AI</span>
                    <strong><i class="fa-solid fa-chevron-down"></i></strong>
                </summary>
                <pre class="dms-log">{{ json_encode(json_decode($result->raw_payload ?: '[]', true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </details>
        </div>
    @empty
        <div class="dms-card">
            <div class="dms-empty">
                Ainda nao existe sugestao AI para este documento.
            </div>
        </div>
    @endforelse
@endsection
