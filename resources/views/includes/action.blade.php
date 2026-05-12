@if(!empty($actions) && is_array($actions))
    @once
        <style>
            /*
             |--------------------------------------------------------------------------
             | LSG Page Actions - Responsive Behaviour
             |--------------------------------------------------------------------------
             | Desktop: keeps normal action buttons with icon + label.
             | Mobile: converts actions into a compact single-line icon toolbar.
             */
            .lsg-page-actions {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 8px;
            }

            .lsg-action-form {
                display: inline-flex;
                align-items: center;
                margin: 0;
            }

            .lsg-action-btn {
                white-space: nowrap;
            }

            .lsg-action-btn__icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            @media (max-width: 767.98px) {
                .app-topbar-inner.has-topbar-actions .topbar-left,
                .app-topbar-inner.has-topbar-actions .breadcrumbs-card,
                .app-topbar-inner.has-topbar-actions .breadcrumbs-actions {
                    width: 100%;
                    max-width: 100%;
                    min-width: 0;
                }

                .app-topbar-inner.has-topbar-actions .breadcrumbs-actions {
                    overflow: hidden;
                }

                .lsg-page-actions {
                    width: 100%;
                    max-width: 100%;
                    display: flex;
                    flex-direction: row;
                    flex-wrap: nowrap;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;

                    overflow-x: auto;
                    overflow-y: hidden;
                    padding: 2px 2px 6px 2px;

                    -webkit-overflow-scrolling: touch;
                    scrollbar-width: thin;
                }

                .lsg-page-actions::-webkit-scrollbar {
                    height: 4px;
                }

                .lsg-page-actions::-webkit-scrollbar-track {
                    background: transparent;
                }

                .lsg-page-actions::-webkit-scrollbar-thumb {
                    background: rgba(148, 163, 184, 0.35);
                    border-radius: 999px;
                }

                body.theme-dark .lsg-page-actions::-webkit-scrollbar-thumb,
                body[data-theme="dark"] .lsg-page-actions::-webkit-scrollbar-thumb {
                    background: rgba(255, 255, 255, 0.22);
                }

                .lsg-page-actions .lsg-action-form {
                    flex: 0 0 auto;
                    display: inline-flex;
                    margin: 0;
                }

                .lsg-page-actions .lsg-action-btn {
                    flex: 0 0 42px;
                    width: 42px;
                    min-width: 42px;
                    max-width: 42px;
                    height: 42px;
                    min-height: 42px;

                    padding: 0 !important;
                    margin: 0 7px;

                    display: inline-flex;
                    align-items: center;
                    justify-content: center;

                    border-radius: 5px;
                }

                .lsg-page-actions .lsg-action-btn__glow {
                    border-radius: inherit;
                }

                .lsg-page-actions .lsg-action-btn__label {
                    display: none !important;
                }

                .lsg-page-actions .lsg-action-btn__icon {
                    margin: 0 !important;
                    width: auto;
                    height: auto;
                }

                .lsg-page-actions .lsg-action-btn__icon i {
                    margin: 0 !important;
                    font-size: 1rem;
                    line-height: 1;
                }
            }
        </style>
    @endonce

    @php
        $renderActionIcon = function ($icon) {
            if (empty($icon)) {
                return '';
            }

            if (str_contains($icon, '<')) {
                return $icon;
            }

            return '<i class="' . e($icon) . '" aria-hidden="true"></i>';
        };

        $resolveTone = function (array $action): string {
            $class = strtolower((string) ($action['class'] ?? ''));
            $key   = strtolower((string) ($action['key'] ?? ''));
            $name  = strtolower((string) ($action['name'] ?? ''));

            if (str_contains($class, 'danger') || $key === 'delete' || str_contains($name, 'delete') || str_contains($name, 'remove')) {
                return 'danger';
            }

            if (str_contains($class, 'warning') || $key === 'edit' || str_contains($name, 'edit')) {
                return 'warning';
            }

            if (str_contains($class, 'success') || $key === 'new' || str_contains($name, 'new') || str_contains($name, 'create') || str_contains($name, 'add')) {
                return 'success';
            }

            if ($key === 'save' || str_contains($name, 'save') || str_contains($class, 'primary')) {
                return 'gold';
            }

            return 'back';
        };
    @endphp

    <div class="lsg-page-actions" role="toolbar" aria-label="Page actions">
        @foreach($actions as $action)
            @php
                $actionType = $action['type'] ?? 'link';
                $actionLabel = $action['label'] ?? $action['name'] ?? 'Action';
                $actionTone = $resolveTone($action);
                $actionClasses = trim('lsg-action-btn lsg-action-btn--' . $actionTone . ' ' . ($action['extra_class'] ?? ''));
                $iconMarkup = $renderActionIcon($action['icon'] ?? null);
            @endphp

            @if($actionType === 'submit')
                <button
                    type="submit"
                    class="{{ $actionClasses }}"
                    title="{{ $actionLabel }}"
                    aria-label="{{ $actionLabel }}"
                    @if(!empty($action['form'])) form="{{ $action['form'] }}" @endif
                >
                    <span class="lsg-action-btn__glow"></span>
                    @if($iconMarkup)
                        <span class="lsg-action-btn__icon">{!! $iconMarkup !!}</span>
                    @endif
                    <span class="lsg-action-btn__label">{{ $actionLabel }}</span>
                </button>
            @elseif($actionType === 'delete')
                <form
                    method="POST"
                    action="{{ $action['url'] ?? '#' }}"
                    class="lsg-action-form"
                    onsubmit="return {{ !empty($action['confirm']) ? "confirm('" . addslashes($action['confirm']) . "')" : 'true' }};"
                >
                    @csrf
                    @method($action['method'] ?? 'DELETE')

                    <button
                        type="submit"
                        class="{{ $actionClasses }}"
                        title="{{ $actionLabel }}"
                        aria-label="{{ $actionLabel }}"
                    >
                        <span class="lsg-action-btn__glow"></span>
                        @if($iconMarkup)
                            <span class="lsg-action-btn__icon">{!! $iconMarkup !!}</span>
                        @endif
                        <span class="lsg-action-btn__label">{{ $actionLabel }}</span>
                    </button>
                </form>
            @else
                <a
                    href="{{ $action['url'] ?? '#' }}"
                    class="{{ $actionClasses }}"
                    title="{{ $actionLabel }}"
                    aria-label="{{ $actionLabel }}"
                >
                    <span class="lsg-action-btn__glow"></span>
                    @if($iconMarkup)
                        <span class="lsg-action-btn__icon">{!! $iconMarkup !!}</span>
                    @endif
                    <span class="lsg-action-btn__label">{{ $actionLabel }}</span>
                </a>
            @endif
        @endforeach
    </div>
@endif
