@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    @if(!empty($missingTables))
        <div class="dms-alert dms-alert--warning">Explorer em safe mode porque existem tabelas em falta.</div>
    @endif

    <div class="dms-card">
        <table class="dms-table document-lsg-datatable">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Workspace</th>
                    <th>Categoria</th>
                    <th>Estado</th>
                    <th>Intelligence</th>
                    <th>Criado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr>
                        <td>
                            <strong>{{ $document->title }}</strong>
                            <span class="dms-muted">{{ $document->mime_type ?: $document->document_type ?: 'document' }}</span>
                        </td>
                        <td>{{ $document->workspace_name ?: '-' }}</td>
                        <td>{{ $document->category_name ?: '-' }}</td>
                        <td>
                            <span class="dms-badge">{{ $document->status ?: '-' }}</span>
                            <span class="dms-badge dms-badge--soft">{{ $document->workflow_state ?: '-' }}</span>
                        </td>
                        <td>
                            <div class="dms-intel">
                                <span class="{{ $document->has_preview ? 'is-ok' : '' }}">Preview</span>
                                <span class="{{ $document->has_ocr ? 'is-ok' : '' }}">OCR</span>
                                <span class="{{ $document->has_embeddings ? 'is-ok' : '' }}">Vector</span>
                            </div>
                        </td>
                        <td>{{ $document->created_at }}</td>
                        <td class="text-right">
                            <a href="{{ route('document-manager.documents.show', $document->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye"></i></a>
                            <a href="{{ route('document-manager.documents.edit', $document->id) }}" class="btn btn-outline-warning btn-sm"><i class="fa-solid fa-pencil"></i></a>
                            <form method="POST" action="{{ route('document-manager.documents.destroy', $document->id) }}" class="d-inline dms-delete-form" data-confirm-title="Remover documento?" data-confirm-text="Esta acao remove o documento do explorer.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7">Sem documentos.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="dms-pagination">{{ $documents->links() }}</div>
    </div>
@endsection
