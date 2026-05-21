@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    @php
        $currentVersion = $document->currentVersion ?? null;
        $previewMime = $currentVersion?->mime_type ?: $document->mime_type;
        $hasInlineFile = ($fileAvailable ?? false) && $currentVersion;
        $fileUrl = $hasInlineFile ? route('document-manager.documents.file', $document->id) : null;
        $previewUrl = $hasInlineFile ? route('document-manager.documents.preview', $document->id) : null;
        $downloadUrl = $hasInlineFile ? route('document-manager.documents.download', $document->id) : null;
        $isImagePreview = $fileUrl && str_starts_with((string) $previewMime, 'image/');
        $isVideoPreview = $fileUrl && str_starts_with((string) $previewMime, 'video/');
        $isFramePreview = $fileUrl && in_array($previewMime, ['application/pdf', 'text/plain', 'text/markdown', 'text/html'], true);
    @endphp

    @php($metadata = $document->metadata ?? [])
    @php($documentTags = $document->relationLoaded('tags') ? $document->tags : collect())

    <div class="dms-document-workspace">
        <div class="dms-card dms-document-sheet-card">
            <div class="dms-card__head">
                <div class="dms-readiness">
                    <div class="{{ $document->has_file ? 'is-ok' : '' }}"><i class="fa-solid fa-file"></i> File</div>
                    <div class="{{ $document->has_preview ? 'is-ok' : '' }}"><i class="fa-solid fa-eye"></i> Preview</div>
                    <div class="{{ $document->has_ocr ? 'is-ok' : '' }}"><i class="fa-solid fa-align-left"></i> OCR</div>
                    <div class="{{ $document->has_embeddings ? 'is-ok' : '' }}"><i class="fa-solid fa-vector-square"></i> Embeddings</div>
                </div>
            </div>

            <div class="dms-preview dms-document-sheet">
                @if($isImagePreview)
                    <a href="{{ $previewUrl }}" class="dms-preview-link">
                        <img src="{{ $fileUrl }}" alt="{{ $document->title }}" class="dms-preview-image dms-preview-image--sheet">
                    </a>
                @elseif($isVideoPreview)
                    <video class="dms-preview-video dms-preview-video--sheet" controls preload="metadata">
                        <source src="{{ $fileUrl }}" type="{{ $previewMime }}">
                    </video>
                @elseif($isFramePreview)
                    <iframe class="dms-preview-frame dms-preview-frame--sheet" src="{{ $fileUrl }}" title="Preview {{ $document->title }}"></iframe>
                @elseif($document->has_file && $currentVersion && !($fileAvailable ?? false))
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <strong>Ficheiro indisponivel no storage</strong>
                    <span>A versao existe na base de dados, mas o ficheiro nao foi encontrado no disco configurado.</span>
                @elseif($document->has_file)
                    <i class="fa-solid fa-file-circle-question"></i>
                    <strong>Preview nao suportado neste formato</strong>
                    <span>O ficheiro existe e pode ser descarregado.</span>
                    @if($downloadUrl)
                        <a href="{{ $downloadUrl }}" class="btn btn-outline-primary btn-sm">
                            <i class="fa-solid fa-download"></i> Download
                        </a>
                    @endif
                @else
                    <i class="fa-regular fa-file"></i>
                    <strong>Sem ficheiro</strong>
                    <span>Este documento existe como objeto operacional sem anexo fisico.</span>
                @endif
            </div>
        </div>

        <div class="dms-document-side">
            <div class="dms-card">
                <div class="dms-card__head">
                    <div>
                        <span class="dms-eyebrow">Dados operacionais</span>
                        <h3>{{ $document->title }}</h3>
                    </div>
                </div>

                <p class="dms-side-description">{{ $document->description ?: 'Sem descricao.' }}</p>

                <div class="dms-chip-row">
                    <span class="dms-badge">{{ $document->workflow_state }}</span>
                    <span class="dms-badge dms-badge--soft">{{ $document->document_type ?: 'document' }}</span>
                    <span class="dms-badge dms-badge--soft">{{ $document->mime_type ?: 'no file' }}</span>
                </div>

                <div class="dms-tag-list">
                    @forelse($documentTags as $tag)
                        <span style="--tag-color: {{ $tag->color ?: '#60a5fa' }}">
                            <i class="fa-solid fa-tag"></i> {{ $tag->name }}
                        </span>
                    @empty
                        <span><i class="fa-solid fa-tag"></i> Sem tags</span>
                    @endforelse
                </div>

                @include('documentmanager::documents.partials.operations', ['document' => $document])

                <div class="dms-subsection">
                    <div class="dms-grid dms-grid--2">
                        <div class="dms-kv-tile"><span>Valor</span><strong>{{ $metadata['document_value'] ?? '-' }} {{ $metadata['currency'] ?? '' }}</strong></div>
                        <div class="dms-kv-tile dms-payment-status dms-payment-status--{{ $metadata['payment_status'] ?? 'undefined' }}"><span>Estado pagamento</span><strong>{{ $metadata['payment_status'] ?? '-' }}</strong></div>
                        <div class="dms-kv-tile"><span>Pago em</span><strong>{{ $metadata['paid_at'] ?? '-' }}</strong></div>
                        <div class="dms-kv-tile"><span>Quem pagou</span><strong>{{ $metadata['paid_by'] ?? '-' }}</strong></div>
                    </div>

                    <div class="dms-kv-list mt-3">
                        <div><span>Metodo</span><strong>{{ $metadata['payment_method'] ?? '-' }}</strong></div>
                        <div><span>Referencia</span><strong>{{ $metadata['payment_reference'] ?? '-' }}</strong></div>
                        <div><span>Notas</span><strong>{{ $metadata['operational_notes'] ?? '-' }}</strong></div>
                    </div>
                </div>
            </div>

            <details class="dms-card dms-collapsible-panel">
                <summary>
                    <span><i class="fa-solid fa-circle-info"></i> Operational context</span>
                    <strong><i class="fa-solid fa-chevron-down"></i></strong>
                </summary>
                <div class="dms-kv-list">
                    <div><span>Workspace</span><strong>{{ optional($document->workspace)->name ?: '-' }}</strong></div>
                    <div><span>Folder</span><strong>{{ optional($document->folder)->name ?: '-' }}</strong></div>
                    <div><span>Category</span><strong>{{ optional($document->category)->name ?: '-' }}</strong></div>
                    <div><span>Size</span><strong>{{ number_format((int) $document->size_bytes / 1024, 1) }} KB</strong></div>
                </div>
            </details>

            <details class="dms-card dms-collapsible-panel">
                <summary>
                    <span><i class="fa-solid fa-code-branch"></i> Versions</span>
                    <strong>{{ $versions->count() }}</strong>
                </summary>
                <table class="dms-table">
                    <thead><tr><th>#</th><th>Ficheiro</th><th>Status</th><th>Checksum</th></tr></thead>
                    <tbody>
                        @forelse($versions as $version)
                            <tr>
                                <td>{{ $version->version_number }}</td>
                                <td>{{ $version->original_name ?: $version->path }}</td>
                                <td><span class="dms-badge">{{ $version->processing_status }}</span></td>
                                <td>{{ $version->checksum ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Sem versoes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </details>

            <details class="dms-card dms-collapsible-panel">
                <summary>
                    <span><i class="fa-solid fa-diagram-project"></i> Semantic relations</span>
                    <strong>{{ $relations->count() }}</strong>
                </summary>
                <table class="dms-table">
                    <thead><tr><th>Tipo</th><th>Target</th><th>Origem</th><th>Conf.</th></tr></thead>
                    <tbody>
                        @forelse($relations as $relation)
                            <tr>
                                <td>{{ $relation->relation_type }}</td>
                                <td>{{ $relation->related_type }} #{{ $relation->related_id ?: $relation->related_document_id }}</td>
                                <td>{{ $relation->source }}</td>
                                <td>{{ $relation->confidence ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Sem relacoes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </details>

            <details class="dms-card dms-collapsible-panel">
                <summary>
                    <span><i class="fa-solid fa-wand-magic-sparkles"></i> AI summaries</span>
                    <strong>{{ $aiSummaries->count() }}</strong>
                </summary>
                @forelse($aiSummaries as $summary)
                    <div class="dms-note">
                        <strong>{{ $summary->summary_type }}</strong>
                        <p>{{ $summary->summary }}</p>
                    </div>
                @empty
                    <div class="dms-empty">Ainda sem resumo AI.</div>
                @endforelse
            </details>

            <details class="dms-card dms-collapsible-panel">
                <summary>
                    <span><i class="fa-solid fa-file-lines"></i> OCR text</span>
                    <strong>{{ $ocrResults->count() }}</strong>
                </summary>
                @forelse($ocrResults as $ocr)
                    <div class="dms-note">
                        <strong>{{ $ocr->provider }} / {{ $ocr->status }}</strong>
                        <p>{{ $ocr->extracted_text ?: $ocr->error_message ?: 'Sem texto extraido.' }}</p>
                    </div>
                @empty
                    <div class="dms-empty">Ainda sem OCR.</div>
                @endforelse
            </details>

            <details class="dms-card dms-collapsible-panel">
                <summary>
                    <span><i class="fa-solid fa-chart-simple"></i> AI analysis</span>
                    <strong>{{ $aiAnalyses->count() }}</strong>
                </summary>
                @forelse($aiAnalyses as $analysis)
                    <div class="dms-note">
                        <strong>{{ $analysis->analysis_type }} / {{ $analysis->status }}</strong>
                        <p>{{ json_encode(json_decode($analysis->classification ?: '[]', true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</p>
                    </div>
                @empty
                    <div class="dms-empty">Ainda sem analise AI.</div>
                @endforelse
            </details>

            <details class="dms-card dms-collapsible-panel">
                <summary>
                    <span><i class="fa-solid fa-clock-rotate-left"></i> Timeline</span>
                    <strong>{{ $timeline->count() }}</strong>
                </summary>
                <div class="dms-timeline">
                    @forelse($timeline as $event)
                        <div>
                            <span>{{ $event->created_at }}</span>
                            <strong>{{ $event->event }}</strong>
                        </div>
                    @empty
                        <div class="dms-empty">Sem eventos.</div>
                    @endforelse
                </div>
            </details>
        </div>
    </div>
@endsection
