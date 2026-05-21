@php
    $categories = config('password-manager.categories', []);
@endphp

<div class="password-manager-card passwordManager-card">
    <div class="password-manager-table-wrap">
        <table class="password-manager-table password-manager-table--lean lsg-datatable">
            <thead>
                <tr>
                    <th class="pm-col-title">Title</th>
                    <th class="pm-col-category">Category</th>
                    <th class="pm-col-url">URL</th>
                    <th class="pm-col-username">Username</th>
                    <th class="pm-col-password">Pass</th>
                    <th class="pm-col-date">Used</th>
                    <th class="text-center" style="width: 76px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td>
                            <div class="pm-table-title">
                                <strong>{{ $entry->title }}</strong>
                            </div>
                        </td>
                        <td>
                            <span class="password-manager-badge">
                                {{ $categories[$entry->category] ?? ($entry->category ?: 'General') }}
                            </span>
                        </td>
                        <td class="pm-table-url">
                            @if($entry->url)
                                <a href="{{ $entry->url }}" target="_blank" rel="noopener noreferrer" class="pm-table-link" title="Open URL in new tab">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    <span>{{ $entry->url }}</span>
                                </a>
                            @else
                                <span class="pm-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($entry->login_username)
                                <button
                                    type="button"
                                    class="pm-copy-inline"
                                    data-copy-value="{{ e($entry->login_username) }}"
                                    data-copy-title="Copy username"
                                    data-copied-title="Username copied"
                                    title="Copy username"
                                >
                                    <i class="fa-solid fa-copy"></i>
                                    <span>{{ $entry->login_username }}</span>
                                </button>
                            @else
                                <span class="pm-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <button
                                type="button"
                                class="pm-copy-inline pm-copy-inline--secret"
                                data-copy-value="{{ e($entry->copy_password ?? '') }}"
                                data-copy-title="Copy password"
                                data-copied-title="Password copied"
                                title="Copy password"
                            >
                                <i class="fa-solid fa-copy"></i>
                            </button>
                        </td>
                        <td>{{ $entry->last_used_at ? $entry->last_used_at->format('d/m/Y H:i') : '—' }}</td>
                        <td>
                            <div class="password-manager-actions password-manager-actions--center">
                                <a href="{{ route('password_manager.edit', $entry) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" title="Edit">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('password_manager.destroy', $entry) }}" class="lsg-action-form" onsubmit="return confirm('Delete this password entry?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="password-manager-mobile-list">
        @forelse($entries as $entry)
            <div class="password-manager-mobile-item passwordManager-card">
                <div class="pm-mobile-item__header">
                    <div>
                        <strong>{{ $entry->title }}</strong>
                        <div class="pm-mobile-item__sub">{{ $categories[$entry->category] ?? ($entry->category ?: 'General') }}</div>
                    </div>
                    <div class="password-manager-actions">
                        <a href="{{ route('password_manager.edit', $entry) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" title="Edit"><i class="fa-solid fa-pencil"></i></a>
                        <form method="POST" action="{{ route('password_manager.destroy', $entry) }}" class="lsg-action-form" onsubmit="return confirm('Delete this password entry?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>

                <div class="pm-mobile-quick-actions">
                    @if($entry->url)
                        <a href="{{ $entry->url }}" target="_blank" rel="noopener noreferrer" class="pm-mobile-action">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            <span>Open URL</span>
                        </a>
                    @endif

                    @if($entry->login_username)
                        <button type="button" class="pm-mobile-action" data-copy-value="{{ e($entry->login_username) }}" data-copy-title="Copy username" data-copied-title="Username copied">
                            <i class="fa-solid fa-user"></i>
                            <span>Copy username</span>
                        </button>
                    @endif

                    <button type="button" class="pm-mobile-action" data-copy-value="{{ e($entry->copy_password ?? '') }}" data-copy-title="Copy password" data-copied-title="Password copied">
                        <i class="fa-solid fa-key"></i>
                        <span>Copy password</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="password-manager-mobile-item passwordManager-card">{{ config('password-manager.ui.empty_state.text', 'No entries found.') }}</div>
        @endforelse
    </div>

</div>
