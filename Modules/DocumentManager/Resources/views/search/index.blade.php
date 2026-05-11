@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    <div class="dms-card">
        <form method="GET" class="dms-searchbar">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Pesquisar metadata, OCR, AI summaries, checksum, entidades ou relacoes">
            <button class="btn btn-outline-primary" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Pesquisar</button>
        </form>
    </div>

    <div class="dms-card">
        <table class="dms-table document-lsg-datatable">
            <thead><tr><th>Documento</th><th>Tipo</th><th>Estado</th><th>Criado</th><th></th></tr></thead>
            <tbody>
                @forelse($documents as $document)
                    <tr>
                        <td>{{ $document->title }}</td>
                        <td>{{ $document->document_type ?: '-' }}</td>
                        <td><span class="dms-badge">{{ $document->workflow_state }}</span></td>
                        <td>{{ $document->created_at }}</td>
                        <td><a href="{{ route('document-manager.documents.show', $document->id) }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Sem resultados.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="dms-pagination">{{ $documents->links() }}</div>
    </div>
@endsection
