<div class="password-manager-card passwordManager-card">
    <div class="password-manager-table-wrap">
        <table class="password-manager-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Login</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 170px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr>
                        <td>
                            <div class="pm-table-title">
                                <strong>{{ $entry->title }}</strong>
                                <span>{{ $entry->account_email ?: 'No email' }}</span>
                            </div>
                        </td>
                        <td>{{ $entry->category ?: '—' }}</td>
                        <td>{{ $entry->login_username ?: '—' }}</td>
                        <td class="pm-table-url">{{ $entry->url ?: '—' }}</td>
                        <td>
                            @if($entry->is_favorite)
                                <span class="password-manager-badge password-manager-badge--favorite">Favorite</span>
                            @else
                                <span class="password-manager-badge password-manager-badge--neutral">Normal</span>
                            @endif
                        </td>
                        <td>
                            <div class="password-manager-actions password-manager-actions--center">
                                <a href="{{ route('password_manager.show', $entry) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="Show">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('password_manager.edit', $entry) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" title="Edit">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                                <a href="{{ route('password_manager.destroy', $entry) }}" class="lsg-action-btn lsg-action-btn--danger"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="pm-empty-state">
                                <strong>{{ config('password-manager.ui.empty_state.title', 'No entries found') }}</strong>
                                <span>{{ config('password-manager.ui.empty_state.text', 'Create a secure entry or adjust the current filters.') }}</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="password-manager-mobile-list">
        @forelse($entries as $entry)
            <div class="password-manager-mobile-item passwordManager-card">
                <div class="pm-mobile-item__header">
                    <div>
                        <strong>{{ $entry->title }}</strong>
                        <div class="pm-mobile-item__sub">{{ $entry->account_email ?: ($entry->login_username ?: 'No login') }}</div>
                    </div>
                    @if($entry->is_favorite)
                        <span class="password-manager-badge password-manager-badge--favorite">Favorite</span>
                    @endif
                </div>
                <div class="pm-mobile-item__category">{{ $entry->category ?: 'No category' }}</div>
                <div class="password-manager-actions" style="margin-top:0.85rem;">
                    <a href="{{ route('password_manager.show', $entry) }}" class="lsg-action-btn lsg-action-btn--primary"><i class="fa-solid fa-eye"></i><span>Show</span></a>
                    <a href="{{ route('password_manager.edit', $entry) }}" class="lsg-action-btn lsg-action-btn--warning"><i class="fa-solid fa-pencil"></i><span>Edit</span></a>
                    <a href="{{ route('password_manager.destroy', $entry) }}" class="lsg-action-btn lsg-action-btn--danger"><i class="fa-solid fa-trash"></i><span>Delete</span></a>
                </div>
            </div>
        @empty
            <div class="password-manager-mobile-item passwordManager-card">{{ config('password-manager.ui.empty_state.text', 'No entries found.') }}</div>
        @endforelse
    </div>

    <div class="pm-pagination-wrap">
        {{ $entries->links() }}
    </div>
</div>
