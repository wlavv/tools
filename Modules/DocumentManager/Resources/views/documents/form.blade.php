@php
    $isCompact = !empty($compact);
@endphp

<div class="dms-field">
    <label>Titulo</label>
    <input type="text" name="title" value="{{ old('title', $document->title ?? '') }}" required>
    @error('title')<span class="dms-error">{{ $message }}</span>@enderror
</div>

<div class="dms-field">
    <label>Descricao</label>
    <textarea name="description" rows="4">{{ old('description', $document->description ?? '') }}</textarea>
</div>

<div class="dms-form-row">
    <div class="dms-field">
        <label>Workspace</label>
        <select name="workspace_id">
            <option value="">Sem workspace</option>
            @foreach($workspaces as $workspace)
                <option value="{{ $workspace->id }}" @selected((string) old('workspace_id', $document->workspace_id ?? '') === (string) $workspace->id)>{{ $workspace->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="dms-field">
        <label>Folder</label>
        <select name="folder_id">
            <option value="">Raiz</option>
            @foreach($folders as $folder)
                <option value="{{ $folder->id }}" @selected((string) old('folder_id', $document->folder_id ?? '') === (string) $folder->id)>{{ $folder->name }}</option>
            @endforeach
        </select>
    </div>
</div>

@php
    $metadata = old('metadata', $document->metadata ?? []);
@endphp

<details class="dms-form-section" open>
    <summary><span>Dados operacionais</span><i class="fa-solid fa-chevron-down"></i></summary>

    <div class="dms-form-row">
        <div class="dms-field">
            <label>Valor do documento</label>
            <input type="number" step="0.01" min="0" name="metadata[document_value]" value="{{ $metadata['document_value'] ?? '' }}" placeholder="0.00">
        </div>

        <div class="dms-field">
            <label>Moeda</label>
            <input type="text" name="metadata[currency]" value="{{ $metadata['currency'] ?? 'EUR' }}" placeholder="EUR">
        </div>
    </div>

    <div class="dms-form-row">
        <div class="dms-field">
            <label>Estado de pagamento</label>
            <select name="metadata[payment_status]">
                @foreach(['' => 'Nao definido', 'pending' => 'Pendente', 'paid' => 'Pago', 'partial' => 'Parcial', 'overdue' => 'Em atraso', 'cancelled' => 'Cancelado'] as $value => $label)
                    <option value="{{ $value }}" @selected(($metadata['payment_status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="dms-field">
            <label>Data de pagamento</label>
            <input type="date" name="metadata[paid_at]" value="{{ $metadata['paid_at'] ?? '' }}">
        </div>
    </div>

    <div class="dms-form-row">
        <div class="dms-field">
            <label>Quem pagou</label>
            <input type="text" name="metadata[paid_by]" value="{{ $metadata['paid_by'] ?? '' }}" placeholder="Pessoa, entidade ou conta">
        </div>

        <div class="dms-field">
            <label>Metodo</label>
            <input type="text" name="metadata[payment_method]" value="{{ $metadata['payment_method'] ?? '' }}" placeholder="Transferencia, cartao, MBWay">
        </div>
    </div>

    <div class="dms-field">
        <label>Referencia de pagamento</label>
        <input type="text" name="metadata[payment_reference]" value="{{ $metadata['payment_reference'] ?? '' }}" placeholder="Referencia bancaria, ID de transacao, fatura">
    </div>

    <div class="dms-field">
        <label>Notas operacionais</label>
        <textarea name="metadata[operational_notes]" rows="{{ $isCompact ? 2 : 3 }}" placeholder="Observacoes uteis para financeiro, compliance ou workflow">{{ $metadata['operational_notes'] ?? '' }}</textarea>
    </div>
</details>

<details class="dms-form-section">
    <summary><span>Classificacao</span><i class="fa-solid fa-chevron-down"></i></summary>

    <div class="dms-field">
        <label>Tags</label>
        @php
            $selectedTags = collect(old('tag_ids', isset($document) && $document ? $document->tags->pluck('id')->toArray() : []))->map(fn($id) => (string) $id)->toArray();
        @endphp
        <select name="tag_ids[]" multiple>
            @foreach(($tags ?? collect()) as $tag)
                <option value="{{ $tag->id }}" @selected(in_array((string) $tag->id, $selectedTags, true))>{{ $tag->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="dms-form-row">
        <div class="dms-field">
            <label>Categoria</label>
            <select name="category_id">
                <option value="">Sem categoria</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $document->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="dms-field">
            <label>Tipo documental</label>
            <input type="text" name="document_type" value="{{ old('document_type', $document->document_type ?? '') }}" placeholder="contract, invoice, manual, certificate">
        </div>
    </div>
</details>
