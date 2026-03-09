<div class="password-manager-card">
    <div class="password-manager-table-wrap">
        <table class="password-manager-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Categoria</th>
                    <th>Login</th>
                    <th>URL</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>
                            <strong>{{ $entry->title }}</strong><br>
                            <small>{{ $entry->account_email ?: 'Sem email' }}</small>
                        </td>
                        <td>{{ $entry->category ?: '—' }}</td>
                        <td>{{ $entry->login_username ?: '—' }}</td>
                        <td>{{ $entry->url ?: '—' }}</td>
                        <td>
                            @if($entry->is_favorite)
                                <span class="password-manager-badge">Favorito</span>
                            @else
                                <span class="password-manager-badge">Normal</span>
                            @endif
                        </td>
                        <td>
                            <div class="password-manager-actions">
                                <a href="{{ route('password_manager.show', $entry) }}" class="password-manager-btn">Ver</a>
                                <a href="{{ route('password_manager.edit', $entry) }}" class="password-manager-btn">Editar</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Sem registos encontrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="password-manager-mobile-list">
        @forelse($entries as $entry)
            <div class="password-manager-mobile-item">
                <strong>{{ $entry->title }}</strong>
                <div style="color:#64748b; margin-top:0.25rem;">{{ $entry->account_email ?: ($entry->login_username ?: 'Sem login') }}</div>
                <div style="margin-top:0.35rem;">{{ $entry->category ?: 'Sem categoria' }}</div>
                <div class="password-manager-actions" style="margin-top:0.75rem;">
                    <a href="{{ route('password_manager.show', $entry) }}" class="password-manager-btn">Ver</a>
                    <a href="{{ route('password_manager.edit', $entry) }}" class="password-manager-btn">Editar</a>
                </div>
            </div>
        @empty
            <div class="password-manager-mobile-item">Sem registos encontrados.</div>
        @endforelse
    </div>

    <div style="margin-top:1rem;">
        {{ $entries->links() }}
    </div>
</div>
