<div class="dms-field">
    <label>Nome</label>
    <input type="text" name="name" value="{{ old('name', $folder->name ?? '') }}" required>
</div>

<div class="dms-form-row">
    <div class="dms-field">
        <label>Workspace</label>
        <select name="workspace_id">
            <option value="">Global</option>
            @foreach($workspaces as $workspace)
                <option value="{{ $workspace->id }}" @selected((string) old('workspace_id', $folder->workspace_id ?? '') === (string) $workspace->id)>{{ $workspace->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dms-field">
        <label>Pasta pai</label>
        <select name="parent_id">
            <option value="">Raiz</option>
            @foreach($folders as $parent)
                @if(!isset($folder) || (int) $parent->id !== (int) ($folder->id ?? 0))
                    <option value="{{ $parent->id }}" @selected((string) old('parent_id', $folder->parent_id ?? '') === (string) $parent->id)>{{ $parent->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>

<div class="dms-form-row">
    <div class="dms-field">
        <label>Path</label>
        <input type="text" name="path" value="{{ old('path', $folder->path ?? '') }}" placeholder="Finance/Invoices">
    </div>
    <div class="dms-field">
        <label>Ordem</label>
        <input type="number" name="sort_order" value="{{ old('sort_order', $folder->sort_order ?? 0) }}">
    </div>
</div>

<input type="hidden" name="depth" value="{{ old('depth', $folder->depth ?? 0) }}">
