@extends('layouts.app')

@section('content')
    @include('password-manager::Includes.css')

    <div class="password-manager-shell">

        @if(session('success'))
            <div class="password-manager-alert">{{ session('success') }}</div>
        @endif

        <div class="password-manager-card passwordManager-card">
            <div class="pm-show-topbar">
                <div>
                    <h2 class="pm-show-title">{{ $entry->title }}</h2>
                    <div class="pm-show-category">{{ $entry->category ?: 'No category' }}</div>
                </div>
            </div>

            <div class="password-manager-grid pm-show-grid">
                <div class="password-manager-meta">
                    <span class="password-manager-meta__label">Email</span>
                    <div>{{ $entry->account_email ?: '—' }}</div>
                </div>

                <div class="password-manager-meta">
                    <span class="password-manager-meta__label">Username</span>
                    <div>{{ $entry->login_username ?: '—' }}</div>
                </div>

                <div class="password-manager-meta">
                    <span class="password-manager-meta__label">URL</span>
                    <div>{{ $entry->url ?: '—' }}</div>
                </div>

                <div class="password-manager-meta">
                    <span class="password-manager-meta__label">Last usage</span>
                    <div>{{ $entry->last_used_at ? $entry->last_used_at->format('d/m/Y H:i') : '—' }}</div>
                </div>

                <div class="password-manager-meta">
                    <span class="password-manager-meta__label">Password</span>
                    <div>**********</div>
                </div>

                <div class="password-manager-meta">
                    <span class="password-manager-meta__label">Secret / token</span>
                    <div>**********</div>
                </div>

                <div class="password-manager-meta password-manager-grid-1">
                    <span class="password-manager-meta__label">Private notes</span>
                    <div>{{ $revealed['notes'] ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    @include('password-manager::Includes.js')
@endsection
