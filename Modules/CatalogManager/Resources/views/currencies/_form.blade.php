<div class="catalog-lsg-form-grid">
    <div class="catalog-lsg-form-group">
        <label>ISO code</label>
        <input type="text" name="iso_code" maxlength="3" value="{{ old('iso_code', $currency->iso_code ?? '') }}" placeholder="EUR">
    </div>
    <div class="catalog-lsg-form-group">
        <label>Nome</label>
        <input type="text" name="name" value="{{ old('name', $currency->name ?? '') }}" placeholder="Euro">
    </div>
    <div class="catalog-lsg-form-group">
        <label>Simbolo</label>
        <input type="text" name="symbol" value="{{ old('symbol', $currency->symbol ?? '') }}" placeholder="EUR">
    </div>
    <div class="catalog-lsg-form-group">
        <label>Taxa conversao para EUR</label>
        <input type="number" step="0.000001" min="0" name="conversion_rate_to_eur" value="{{ old('conversion_rate_to_eur', $currency->conversion_rate_to_eur ?? 1) }}">
    </div>
    <div class="catalog-lsg-form-group">
        <label>Posicao</label>
        <input type="number" min="0" step="1" name="position" value="{{ old('position', $currency->position ?? 0) }}">
    </div>
    <div class="catalog-lsg-form-group">
        <label>
            <input type="checkbox" name="active" value="1" @checked(old('active', $currency->active ?? true))>
            Ativo
        </label>
    </div>
</div>

<div class="catalog-lsg-form-actions">
    <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
    <a href="{{ route('catalog-manager.currencies.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-angle-left"></i> Voltar
    </a>
</div>
