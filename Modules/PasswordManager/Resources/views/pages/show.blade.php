@extends('layouts.app')

@section('content')
    @include('password-manager::Includes.css')

    <div class="password-manager-page passwordManager-card">
        <div class="password-manager-shell">
            @include('password-manager::Includes._components.header')

            @if(session('success'))
                <div class="password-manager-alert">{{ session('success') }}</div>
            @endif

            <div class="password-manager-card">
                <div style="display:flex; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
                    <div>
                        <h2 style="margin:0 0 0.35rem 0;">{{ $entry->title }}</h2>
                        <div style="color:#64748b;">{{ $entry->category ?: 'Sem categoria' }}</div>
                    </div>

                    <div class="password-manager-actions">
                        <a href="{{ route('password_manager.edit', $entry) }}" class="password-manager-btn">Editar</a>
                        <a href="{{ route('password_manager.index') }}" class="password-manager-btn">Voltar</a>
                    </div>
                </div>

                <div class="password-manager-grid" style="margin-top:1rem;">
                    <div class="password-manager-meta">
                        <strong>Email</strong> {{ $entry->account_email ?: '—' }}
                    </div>

                    <div class="password-manager-meta">
                        <strong>Username</strong>
                        <div>{{ $entry->login_username ?: '—' }}</div>
                    </div>

                    <div class="password-manager-meta">
                        <strong>URL</strong>
                        <div>{{ $entry->url ?: '—' }}</div>
                    </div>

                    <div class="password-manager-meta">
                        <strong>Última utilização</strong>
                        <div>{{ $entry->last_used_at ? $entry->last_used_at->format('d/m/Y H:i') : '—' }}</div>
                    </div>

                    <div class="password-manager-meta">
                        <strong>Password</strong>
                        <div>**********</div>
                    </div>

                    <div class="password-manager-meta">
                        <strong>Secret / token</strong>
                        <div>**********</div>
                    </div>

                    <div class="password-manager-meta password-manager-grid-1">
                        <strong>Notas</strong>
                        <div>{{ $revealed['notes'] ?: '—' }}</div>
                    </div>
                </div>

                <div class="password-manager-actions" style="margin-top:1rem;">
                    <form method="POST" action="{{ route('password_manager.destroy', $entry) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="password-manager-btn password-manager-btn-danger" onclick="return confirm('Remover este registo?')">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('password-manager::Includes.js')
@endsection
