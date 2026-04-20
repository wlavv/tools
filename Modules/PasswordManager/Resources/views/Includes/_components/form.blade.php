<div class="password-manager-form-card passwordManager-card">
    <form id="lsg-form" method="POST" action="{{ $action }}" class="password-manager-grid">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div>
            <label class="password-manager-label">Title</label>
            <input type="text" name="title" value="{{ old('title', $entry->title ?? '') }}" class="password-manager-input" required>
        </div>

        <div>
            <label class="password-manager-label">Category</label>
            <input type="text" name="category" value="{{ old('category', $entry->category ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">URL</label>
            <input type="url" name="url" value="{{ old('url', $entry->url ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">Account email</label>
            <input type="email" name="account_email" value="{{ old('account_email', $entry->account_email ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">Username / login</label>
            <input type="text" name="login_username" value="{{ old('login_username', $entry->login_username ?? '') }}" class="password-manager-input">
        </div>

        <div>
            <label class="password-manager-label">Password</label>
            <div class="pm-password-field">
                <input
                    id="password-field"
                    type="password"
                    name="password"
                    value="{{ old('password', $revealed['password'] ?? '') }}"
                    class="password-manager-input"
                    {{ $method === 'POST' ? 'required' : '' }}
                >
                <button
                    type="button"
                    class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"
                    data-secret-toggle="password-field"
                    data-show-title="Show password"
                    data-hide-title="Hide password"
                    title="Show password"
                    aria-label="Show password"
                >
                    <span class="lsg-action-btn__icon">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </span>
                </button>
                <button
                    type="button"
                    class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"
                    data-copy-target="password-field"
                    data-copy-title="Copy password"
                    data-copied-title="Copied"
                    title="Copy password"
                    aria-label="Copy password"
                >
                    <span class="lsg-action-btn__icon">
                        <i class="fa-solid fa-copy" aria-hidden="true"></i>
                    </span>
                </button>
            </div>
        </div>

        <div>
            <label class="password-manager-label">Secret / token</label>
            <div class="pm-password-field">
                <input
                    id="secret-field"
                    type="password"
                    name="secret"
                    value="{{ old('secret', $revealed['secret'] ?? '') }}"
                    class="password-manager-input"
                >
                <button
                    type="button"
                    class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"
                    data-secret-toggle="secret-field"
                    data-show-title="Show secret"
                    data-hide-title="Hide secret"
                    title="Show secret"
                    aria-label="Show secret"
                >
                    <span class="lsg-action-btn__icon">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </span>
                </button>
                <button
                    type="button"
                    class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"
                    data-copy-target="secret-field"
                    data-copy-title="Copy secret"
                    data-copied-title="Copied"
                    title="Copy secret"
                    aria-label="Copy secret"
                >
                    <span class="lsg-action-btn__icon">
                        <i class="fa-solid fa-copy" aria-hidden="true"></i>
                    </span>
                </button>
            </div>
        </div>

        <div>
            <label class="password-manager-label">Favorite</label>
            <select name="is_favorite" class="password-manager-input">
                <option value="0" {{ (string) old('is_favorite', (int) ($entry->is_favorite ?? 0)) === '0' ? 'selected' : '' }}>No</option>
                <option value="1" {{ (string) old('is_favorite', (int) ($entry->is_favorite ?? 0)) === '1' ? 'selected' : '' }}>Yes</option>
            </select>
        </div>

        <div class="password-manager-grid-1">
            <label class="password-manager-label">Private notes</label>
            <div class="pm-password-field pm-password-field--textarea">
                <textarea
                    id="notes-field"
                    name="notes"
                    class="password-manager-textarea pm-secret-textarea"
                >{{ old('notes', $revealed['notes'] ?? '') }}</textarea>
                <button
                    type="button"
                    class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"
                    data-secret-toggle="notes-field"
                    data-show-title="Show private notes"
                    data-hide-title="Hide private notes"
                    title="Show private notes"
                    aria-label="Show private notes"
                >
                    <span class="lsg-action-btn__icon">
                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    </span>
                </button>
                <button
                    type="button"
                    class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"
                    data-copy-target="notes-field"
                    data-copy-title="Copy private notes"
                    data-copied-title="Copied"
                    title="Copy private notes"
                    aria-label="Copy private notes"
                >
                    <span class="lsg-action-btn__icon">
                        <i class="fa-solid fa-copy" aria-hidden="true"></i>
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>
