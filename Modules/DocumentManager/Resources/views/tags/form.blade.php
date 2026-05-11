<div class="dms-field">
    <label>Nome</label>
    <input type="text" name="name" value="{{ old('name', $tag->name ?? '') }}" required>
</div>

<div class="dms-form-row">
    <div class="dms-field">
        <label>Tipo</label>
        <input type="text" name="type" value="{{ old('type', $tag->type ?? 'manual') }}" placeholder="manual, ai, compliance">
    </div>
    <div class="dms-field">
        <label>Cor</label>
        <input type="color" name="color" value="{{ old('color', $tag->color ?? '#60a5fa') }}">
    </div>
</div>
