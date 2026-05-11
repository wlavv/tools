<div class="dms-document-ops">
    <a href="{{ route('document-manager.documents.edit', $document->id) }}" class="btn btn-outline-warning btn-sm">
        <i class="fa-solid fa-pencil"></i> Editar
    </a>

    <form method="POST" action="{{ route('document-manager.documents.process', [$document->id, 'all']) }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-rotate"></i> Processar tudo
        </button>
    </form>

    <form method="POST" action="{{ route('document-manager.documents.process', [$document->id, 'ocr']) }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-file-lines"></i> OCR
        </button>
    </form>

    <form method="POST" action="{{ route('document-manager.documents.process', [$document->id, 'summary']) }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-wand-magic-sparkles"></i> Resumo
        </button>
    </form>

    <form method="POST" action="{{ route('document-manager.documents.process', [$document->id, 'analysis']) }}">
        @csrf
        <button type="submit" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-chart-simple"></i> Analise
        </button>
    </form>

    <form method="POST" action="{{ route('document-manager.documents.workflow', $document->id) }}" class="dms-workflow-form">
        @csrf
        <select name="workflow_state" onchange="this.form.submit()">
            @foreach(config('documentmanager.workflow_states', []) as $state)
                <option value="{{ $state }}" @selected($document->workflow_state === $state)>{{ str_replace('_', ' ', $state) }}</option>
            @endforeach
        </select>
    </form>

    <form method="POST" action="{{ route('document-manager.documents.destroy', $document->id) }}" onsubmit="return confirm('Remover este documento?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger btn-sm">
            <i class="fa-solid fa-trash"></i>
        </button>
    </form>
</div>
