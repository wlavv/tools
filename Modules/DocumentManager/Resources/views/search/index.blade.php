@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-search-results-layout">
        <div class="dms-card">
            <table class="dms-table document-lsg-datatable">
                <thead><tr><th>Documento</th><th>Tipo</th><th>Estado</th><th>Criado</th><th></th></tr></thead>
                <tbody>
                    @forelse($documents as $document)
                        @php($previewUrl = request()->fullUrlWithQuery(['preview_id' => $document->id]))
                        <tr class="dms-search-row {{ (int) ($selectedDocument->id ?? 0) === (int) $document->id ? 'is-active' : '' }}"
                            onclick="window.location.href='{{ $previewUrl }}'">
                            <td>{{ $document->title }}</td>
                            <td>{{ $document->document_type ?: '-' }}</td>
                            <td>
                                <span class="dms-badge">{{ $document->status ?: '-' }}</span>
                                <span class="dms-badge dms-badge--soft">{{ $document->workflow_state ?: '-' }}</span>
                            </td>
                            <td>{{ $document->created_at }}</td>
                            <td class="text-right" onclick="event.stopPropagation();">
                                <a href="{{ route('document-manager.documents.show', $document->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye"></i></a>
                                <form method="POST" action="{{ route('document-manager.documents.destroy', $document->id) }}" class="d-inline dms-delete-form" data-confirm-title="Remover documento?" data-confirm-text="Esta acao remove o documento dos resultados.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Sem resultados.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="dms-pagination">{{ $documents->links() }}</div>
        </div>

        <div class="dms-card dms-search-preview dms-search-preview--document-only">
            @if($selectedDocument)
                @if(($selectedDocument->has_file ?? false) && ($selectedDocument->has_preview ?? false))
                    <iframe src="{{ route('document-manager.documents.file', $selectedDocument->id) }}" title="Preview {{ $selectedDocument->title }}"></iframe>
                @else
                    <div class="dms-empty-state">
                        <i class="fa-regular fa-file"></i>
                        <strong>Preview indisponivel</strong>
                        <span>Este documento nao tem ficheiro inline disponivel.</span>
                    </div>
                @endif
            @else
                <div class="dms-empty-state">
                    <i class="fa-regular fa-file"></i>
                    <strong>Sem documento selecionado</strong>
                    <span>Escolhe uma linha para ver o preview.</span>
                </div>
            @endif
        </div>
    </div>
@endsection
