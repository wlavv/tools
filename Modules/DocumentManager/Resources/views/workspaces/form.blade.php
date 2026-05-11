<div class="dms-field">
    <label>Nome</label>
    <input type="text" name="name" value="{{ old('name', $workspace->name ?? '') }}" required>
</div>

<div class="dms-form-row">
    <div class="dms-field">
        <label>Tipo</label>
        <input type="text" name="type" value="{{ old('type', $workspace->type ?? 'operational') }}" placeholder="finance, legal, supplier">
    </div>

    <div class="dms-field">
        <label>Icone FontAwesome</label>
        <input type="text" name="icon" value="{{ old('icon', $workspace->icon ?? 'fa-solid fa-layer-group') }}">
    </div>
</div>

<div class="dms-field">
    <label>Descricao</label>
    <textarea name="description" rows="4">{{ old('description', $workspace->description ?? '') }}</textarea>
</div>

<label class="dms-check">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $workspace->is_active ?? true))>
    <span>Workspace ativo</span>
</label>
