<form method="POST" action="{{ route('document-manager.documents.store') }}" enctype="multipart/form-data" class="dms-quick-upload">
    @csrf
    <input type="hidden" name="source_module" value="{{ $sourceModule ?? $contextWorkspace ?? 'document-manager' }}">

    <div class="dms-quick-upload__layout">
        <div class="dms-quick-upload__fields">
            <div class="dms-quick-upload__grid">
                <div class="dms-field">
                    <label>Titulo</label>
                    <input type="text" name="title" placeholder="Ex: Contrato fornecedor 2026" required>
                </div>

                <div class="dms-field">
                    <label>Workspace</label>
                    <select name="workspace_id">
                        <option value="">Sem workspace</option>
                        @foreach(($workspaces ?? collect()) as $workspace)
                            <option value="{{ $workspace->id }}" @selected((string) $selectedWorkspace === (string) $workspace->id)>{{ $workspace->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="dms-field">
                    <label>Folder</label>
                    <select name="folder_id">
                        <option value="">Raiz</option>
                        @foreach(($folders ?? collect()) as $folder)
                            <option value="{{ $folder->id }}" data-workspace-id="{{ $folder->workspace_id }}" @selected((string) $selectedFolder === (string) $folder->id)>{{ $folder->path ?: $folder->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="dms-field">
                    <label>Categoria</label>
                    <select name="category_id">
                        <option value="">Sem categoria</option>
                        @foreach(($categories ?? collect()) as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="dms-field">
                    <label>Tipo</label>
                    <input type="text" name="document_type" placeholder="invoice, contract, manual">
                </div>
            </div>
        </div>

        <label class="dms-upload-zone dms-upload-zone--compact" for="{{ $uploadId }}FileInput">
            <i class="fa-solid fa-file-arrow-up"></i>
            <strong>Selecionar ficheiro</strong>
            <span>Tambem podes arrastar o ficheiro para aqui.</span>
            <input id="{{ $uploadId }}FileInput" type="file" name="file">
        </label>
    </div>

    <div class="dms-actions dms-actions--right">
        <button type="submit" class="btn btn-outline-success">
            <i class="fa-solid fa-cloud-arrow-up"></i> Enviar
        </button>
    </div>
</form>
