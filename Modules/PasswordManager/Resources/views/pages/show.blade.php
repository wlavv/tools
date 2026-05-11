@extends('layouts.app')

@section('content')
    @include('password-manager::Includes.css')

    @php
        $categories = config('password-manager.categories', []);
        $categoryLabel = $categories[$entry->category] ?? ($entry->category ?: 'Sem categoria');
    @endphp

    <div class="password-manager-page passwordManager-card password-manager-shell password-manager-shell--form">
        <div class="password-manager-shell">

            @if(session('success'))
                <div class="password-manager-alert">{{ session('success') }}</div>
            @endif

            <div>
                <div class="pm-show-header">
                    <div>
                        <h2 class="pm-show-title">{{ $entry->title }}</h2>
                        <div class="pm-show-category">{{ $categoryLabel }}</div>
                    </div>
                </div>

                <div class="password-manager-grid pm-show-grid">
                    <div class="password-manager-meta">
                        <strong>Username / login</strong>
                        <div>{{ $entry->login_username ?: '—' }}</div>
                    </div>

                    <div class="password-manager-meta">
                        <strong>URL / Host</strong>
                        <div>{{ $entry->url ?: '—' }}</div>
                    </div>

                    <div class="password-manager-meta">
                        <strong>Category</strong>
                        <div>{{ $categoryLabel }}</div>
                    </div>

                    <div class="password-manager-meta">
                        <strong>Última utilização</strong>
                        <div>{{ $entry->last_used_at ? $entry->last_used_at->format('d/m/Y H:i') : '—' }}</div>
                    </div>

                    <div class="password-manager-meta password-manager-grid-1">
                        <strong>Password</strong>
                        <div class="pm-password-field pm-password-field--meta">
                            <input
                                id="password-field"
                                type="password"
                                class="password-manager-input"
                                value="{{ $revealed['password'] ?? '' }}"
                                readonly
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
                                <span class="lsg-action-btn__icon"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
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
                                <span class="lsg-action-btn__icon"><i class="fa-solid fa-copy" aria-hidden="true"></i></span>
                            </button>
                        </div>
                    </div>

                    <div class="password-manager-meta password-manager-grid-1">
                        <strong>Private notes</strong>
                        <div class="pm-password-field pm-password-field--textarea pm-password-field--meta-notes">
                            <textarea
                                id="notes-field"
                                class="password-manager-textarea pm-secret-textarea"
                                readonly
                            >{{ $revealed['notes'] ?: '' }}</textarea>
                            <button
                                type="button"
                                class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"
                                data-secret-toggle="notes-field"
                                data-show-title="Show private notes"
                                data-hide-title="Hide private notes"
                                title="Show private notes"
                                aria-label="Show private notes"
                            >
                                <span class="lsg-action-btn__icon"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
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
                                <span class="lsg-action-btn__icon"><i class="fa-solid fa-copy" aria-hidden="true"></i></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('password-manager::Includes.js')
@endsection
