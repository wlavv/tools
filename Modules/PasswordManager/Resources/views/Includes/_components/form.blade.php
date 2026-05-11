@php
    $categories = config('password-manager.categories', []);
    $selectedCategory = old('category', $entry->category ?? 'general');
@endphp

<div class="password-manager-form-card passwordManager-card password-manager-form-card--narrow">
    <form id="lsg-form" method="POST" action="{{ $action }}" class="password-manager-form password-manager-form--vertical">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="pm-form-header-compact">
            <span class="pm-form-section__icon"><i class="fa-solid fa-key"></i></span>
            <div>
                <strong>{{ $method === 'POST' ? 'New password entry' : 'Edit password entry' }}</strong>
                <span>Only the essential access fields are visible.</span>
            </div>
        </div>

        <div class="pm-form-row">
            <label class="password-manager-label">Title</label>
            <input type="text" name="title" value="{{ old('title', $entry->title ?? '') }}" class="password-manager-input" required>
            @error('title') <div class="pm-field-error">{{ $message }}</div> @enderror
        </div>

        <div class="pm-form-row">
            <label class="password-manager-label">Category</label>
            <select name="category" class="password-manager-input lsg-select2">
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" {{ (string) $selectedCategory === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('category') <div class="pm-field-error">{{ $message }}</div> @enderror
        </div>

        <div class="pm-form-row">
            <label class="password-manager-label">URL / Host</label>
            <input type="url" name="url" value="{{ old('url', $entry->url ?? '') }}" class="password-manager-input" placeholder="https://example.com">
            @error('url') <div class="pm-field-error">{{ $message }}</div> @enderror
        </div>

        <div class="pm-form-row">
            <label class="password-manager-label">Username / login</label>
            <div class="pm-password-field pm-password-field--single-action">
                <input id="username-field" type="text" name="login_username" value="{{ old('login_username', $entry->login_username ?? '') }}" class="password-manager-input" autocomplete="off">
                <button type="button" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact" data-copy-target="username-field" data-copy-title="Copy username" data-copied-title="Copied" title="Copy username" aria-label="Copy username">
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-copy" aria-hidden="true"></i></span>
                </button>
            </div>
            @error('login_username') <div class="pm-field-error">{{ $message }}</div> @enderror
        </div>

        <div class="pm-form-row">
            <label class="password-manager-label">Password</label>
            <div class="pm-password-field">
                <input
                    id="password-field"
                    type="password"
                    name="password"
                    value="{{ old('password', $revealed['password'] ?? '') }}"
                    class="password-manager-input"
                    autocomplete="new-password"
                    {{ $method === 'POST' ? 'required' : '' }}
                >
                <button type="button" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" data-secret-toggle="password-field" data-show-title="Show password" data-hide-title="Hide password" title="Show password" aria-label="Show password">
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
                </button>
                <button type="button" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact" data-copy-target="password-field" data-copy-title="Copy password" data-copied-title="Copied" title="Copy password" aria-label="Copy password">
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-copy" aria-hidden="true"></i></span>
                </button>
            </div>
            @error('password') <div class="pm-field-error">{{ $message }}</div> @enderror
        </div>

        <div class="pm-form-row">
            <label class="password-manager-label">Private notes</label>
            <div class="pm-password-field pm-password-field--textarea">
                <textarea id="notes-field" name="notes" class="password-manager-textarea pm-secret-textarea">{{ old('notes', $revealed['notes'] ?? '') }}</textarea>
                <button type="button" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" data-secret-toggle="notes-field" data-show-title="Show private notes" data-hide-title="Hide private notes" title="Show private notes" aria-label="Show private notes">
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
                </button>
                <button type="button" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact" data-copy-target="notes-field" data-copy-title="Copy private notes" data-copied-title="Copied" title="Copy private notes" aria-label="Copy private notes">
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-copy" aria-hidden="true"></i></span>
                </button>
            </div>
            @error('notes') <div class="pm-field-error">{{ $message }}</div> @enderror
        </div>
    </form>
</div>
