@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    @if(!empty($missingTables))
        <div class="dms-alert dms-alert--warning">
            Existem tabelas em falta. O modulo esta em safe mode. Corre as migrations ou abre Diagnostics.
        </div>
    @endif

    <div class="dms-dashboard-panel-line">
        <div class="dms-panel dms-panel--primary">
            <div class="dms-panel__icon"><i class="fa-solid fa-file-lines"></i></div>
            <div><span>Documentos</span><strong>{{ $stats['documents'] ?? 0 }}</strong></div>
        </div>

        <div class="dms-panel dms-panel--primary">
            <div class="dms-panel__icon"><i class="fa-solid fa-layer-group"></i></div>
            <div><span>Workspaces</span><strong>{{ $stats['workspaces'] ?? 0 }}</strong></div>
        </div>

        <div class="dms-panel dms-panel--warning">
            <div class="dms-panel__icon"><i class="fa-solid fa-clipboard-check"></i></div>
            <div><span>Workflow</span><strong>{{ $stats['pending_workflow'] ?? 0 }}</strong></div>
        </div>

        <div class="dms-panel dms-panel--warning">
            <div class="dms-panel__icon"><i class="fa-solid fa-folder-open"></i></div>
            <div><span>Sem categoria</span><strong>{{ $stats['uncategorized'] ?? 0 }}</strong></div>
        </div>

        @foreach($panels as $panel)
            <div class="dms-panel dms-panel--{{ $panel['tone'] ?? 'primary' }}">
                <div class="dms-panel__icon"><i class="{{ $panel['icon'] }}"></i></div>
                <div>
                    <span>{{ $panel['label'] }}</span>
                    <strong>{{ $panel['count'] }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="dms-dashboard-main">
        <div class="dms-card">
            <div class="dms-card__head">
                <div>
                    <span class="dms-eyebrow">Recent activity</span>
                    <h3>Ultimos documentos uploaded</h3>
                </div>
                <a href="{{ route('document-manager.documents.index') }}" class="btn btn-outline-primary">
                    <i class="fa-solid fa-folder-open"></i> Explorer
                </a>
            </div>

            <table class="dms-table document-lsg-datatable">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Workflow</th>
                        <th>Criado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestDocuments as $document)
                        <tr>
                            <td><a href="{{ route('document-manager.documents.show', $document->id) }}">{{ $document->title }}</a></td>
                            <td>{{ $document->document_type ?: '-' }}</td>
                            <td><span class="dms-badge">{{ $document->status ?: '-' }}</span></td>
                            <td><span class="dms-badge dms-badge--soft">{{ $document->workflow_state ?: '-' }}</span></td>
                            <td>{{ $document->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Sem documentos ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
