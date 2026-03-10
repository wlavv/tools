<div class="password-manager-form-card">
    <form method="POST" action="{{ $action }}" class="password-manager-grid">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div>
            <label class="password-manager-label">Título</label>
            <input type="text" name="title" value="{{ old('title', $entry->title ?? '') }}" class="password-manager-input" required>
        </div>

        <div>
            <label class="password-manager-label">Categoria</label>
            <input type="text" name="category" value="{{ old('category', $entry->category ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">URL</label>
            <input type="url" name="url" value="{{ old('url', $entry->url ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">Email da conta</label>
            <input type="email" name="account_email" value="{{ old('account_email', $entry->account_email ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">Username / login</label>
            <input type="text" name="login_username" value="{{ old('login_username', $entry->login_username ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">Password</label>
            <div style="display:grid; grid-template-columns:1fr auto; gap:0.5rem;">
                <input id="password-field" type="password" name="password" value="{{ old('password', $revealed['password'] ?? '') }}" class="password-manager-input" {{ $method === 'POST' ? 'required' : '' }}>
                <button type="button" class="password-manager-btn password-manager-btn-primary" data-password-toggle="password-field"><i class="fa-solid fa-eye"></i></button>
            </div>
        </div>

        <div>
            <label class="password-manager-label">Secret / token</label>
            <input type="text" name="secret" value="{{ old('secret', $revealed['secret'] ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">Favorito</label>
            <select name="is_favorite" class="password-manager-input">
                <option value="0" {{ (string) old('is_favorite', (int) ($entry->is_favorite ?? 0)) === '0' ? 'selected' : '' }}>Não</option>
                <option value="1" {{ (string) old('is_favorite', (int) ($entry->is_favorite ?? 0)) === '1' ? 'selected' : '' }}>Sim</option>
            </select>
        </div>

        <div class="password-manager-grid-1">
            <label class="password-manager-label">Notas privadas</label>
            <textarea name="notes" class="password-manager-textarea">{{ old('notes', $revealed['notes'] ?? '') }}</textarea>
        </div>

        <div class="password-manager-grid-1">
            <div class="password-manager-actions">
                <button type="submit" class="password-manager-btn password-manager-btn-primary"><i class="fa-regular fa-floppy-disk" style="font-size: 20px;"></i></button>
                <a href="{{ route('password_manager.index') }}" class="password-manager-btn"><i class="fa-solid fa-arrow-rotate-left"></i></a>
            </div>
        </div>
    </form>
</div>
