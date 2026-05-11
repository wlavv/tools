<div class="dms-field">
    <label>Nome</label>
    <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required>
</div>

<div class="dms-form-row">
    <div class="dms-field">
        <label>Workspace</label>
        <select name="workspace_id">
            <option value="">Global</option>
            @foreach($workspaces as $workspace)
                <option value="{{ $workspace->id }}" @selected((string) old('workspace_id', $category->workspace_id ?? '') === (string) $workspace->id)>{{ $workspace->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="dms-field">
        <label>Categoria pai</label>
        <select name="parent_id">
            <option value="">Sem pai</option>
            @foreach($categories as $parent)
                @if(!isset($category) || (int) $parent->id !== (int) ($category->id ?? 0))
                    <option value="{{ $parent->id }}" @selected((string) old('parent_id', $category->parent_id ?? '') === (string) $parent->id)>{{ $parent->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>

<div class="dms-form-row">
    <div class="dms-field">
        <label>Cor</label>
        <input type="color" name="color" value="{{ old('color', $category->color ?? '#d4a017') }}">
    </div>
    <div class="dms-field">
        <label>Icone FontAwesome</label>
        <input type="text" name="icon" value="{{ old('icon', $category->icon ?? 'fa-solid fa-folder') }}">
    </div>
</div>
