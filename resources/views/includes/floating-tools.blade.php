<div class="lsg-floating-tools" id="lsgFloatingTools">
    @php
        $floatingNotificationsCount = $floatingNotificationsCount ?? null;

        if ($floatingNotificationsCount === null && auth()->check()) {
            try {
                if (class_exists(\Modules\Notifications\Models\NotificationRecipient::class)) {
                    $floatingNotificationsCount = \Modules\Notifications\Models\NotificationRecipient::query()
                        ->where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->whereNull('dismissed_at')
                        ->count();
                } else {
                    $floatingNotificationsCount = 0;
                }
            } catch (\Throwable $e) {
                $floatingNotificationsCount = 0;
            }
        }

        $floatingNotificationsCount = (int) ($floatingNotificationsCount ?? 0);
    @endphp

    <style>
        /* =========================================================
           LSG FLOATING TOOLS
           Single-file version: HTML + CSS + JS
           ========================================================= */

        :root {
            --lsg-gold: #d4af37;
            --lsg-gold-soft: rgba(212, 175, 55, 0.16);
            --lsg-gold-border: rgba(212, 175, 55, 0.52);
            --lsg-gold-glow: rgba(212, 175, 55, 0.20);

            --lsg-ft-width: 60px;
            --lsg-ft-max-height: 400px;
            --lsg-ft-right: 18px;
            --lsg-ft-radius: 24px;
            --lsg-ft-item-size: 42px;
            --lsg-ft-gap: 10px;
            --lsg-ft-zindex: 1045;
        }

        body.theme-light,
        body[data-theme="light"] {
            --lsg-ft-bg:
                linear-gradient(
                    180deg,
                    rgba(255, 255, 255, 0.98) 0%,
                    rgba(248, 250, 252, 0.96) 100%
                );
            --lsg-ft-border: var(--lsg-gold-border);
            --lsg-ft-shadow:
                0 26px 62px rgba(15, 23, 42, 0.22),
                0 10px 26px rgba(15, 23, 42, 0.14),
                0 0 0 1px rgba(212, 175, 55, 0.12);
            --lsg-ft-shadow-hover:
                0 30px 72px rgba(15, 23, 42, 0.25),
                0 14px 34px rgba(15, 23, 42, 0.16),
                0 0 20px rgba(212, 175, 55, 0.20);
            --lsg-ft-icon: #243248;
            --lsg-ft-icon-hover: #0f1728;
            --lsg-ft-item-bg: rgba(37, 99, 235, 0.07);
            --lsg-ft-item-hover-bg: rgba(212, 175, 55, 0.18);
            --lsg-ft-item-active-bg: rgba(212, 175, 55, 0.24);
            --lsg-ft-item-border: rgba(15, 23, 42, 0.06);
            --lsg-ft-item-border-hover: rgba(212, 175, 55, 0.38);
            --lsg-ft-badge-bg: #ef4444;
            --lsg-ft-badge-text: #ffffff;
            --lsg-ft-outline: rgba(255, 255, 255, 0.82);
        }

        body.theme-dark,
        body[data-theme="dark"] {
            --lsg-ft-bg:
                linear-gradient(
                    180deg,
                    rgba(18, 26, 38, 0.98) 0%,
                    rgba(15, 23, 35, 0.96) 100%
                );
            --lsg-ft-border: rgba(212, 175, 55, 0.46);
            --lsg-ft-shadow:
                0 30px 72px rgba(0, 0, 0, 0.58),
                0 12px 32px rgba(0, 0, 0, 0.36),
                0 0 0 1px rgba(212, 175, 55, 0.14);
            --lsg-ft-shadow-hover:
                0 34px 82px rgba(0, 0, 0, 0.62),
                0 16px 38px rgba(0, 0, 0, 0.42),
                0 0 22px rgba(212, 175, 55, 0.18);
            --lsg-ft-icon: #d7e1f0;
            --lsg-ft-icon-hover: #ffffff;
            --lsg-ft-item-bg: rgba(255, 255, 255, 0.06);
            --lsg-ft-item-hover-bg: rgba(212, 175, 55, 0.16);
            --lsg-ft-item-active-bg: rgba(212, 175, 55, 0.22);
            --lsg-ft-item-border: rgba(255, 255, 255, 0.06);
            --lsg-ft-item-border-hover: rgba(212, 175, 55, 0.34);
            --lsg-ft-badge-bg: #ff5b5b;
            --lsg-ft-badge-text: #ffffff;
            --lsg-ft-outline: rgba(255, 255, 255, 0.08);
        }

        #lsgFloatingTools {
            position: fixed;
            right: var(--lsg-ft-right);
            top: 50%;
            transform: translateY(-50%);
            z-index: var(--lsg-ft-zindex);

            width: var(--lsg-ft-width);
            max-height: var(--lsg-ft-max-height);
            padding: 12px 8px;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: var(--lsg-ft-gap);

            border-radius: 5px;
            border: 1px solid var(--lsg-ft-border);
            background: var(--lsg-ft-bg);
            box-shadow: var(--lsg-ft-shadow);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            isolation: isolate;

            transition:
                box-shadow 0.22s ease,
                border-color 0.22s ease,
                transform 0.22s ease;
        }

        #lsgFloatingTools:hover {
            box-shadow: var(--lsg-ft-shadow-hover);
            border-color: rgba(212, 175, 55, 0.68);
        }

        #lsgFloatingTools::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            pointer-events: none;
            box-shadow:
                inset 0 1px 0 var(--lsg-ft-outline),
                inset 0 0 0 1px rgba(255, 255, 255, 0.02);
        }

        #lsgFloatingTools::after {
            content: "";
            position: absolute;
            inset: -1px;
            z-index: -1;
            border-radius: inherit;
            pointer-events: none;
            background:
                radial-gradient(
                    circle at 50% 0%,
                    rgba(212, 175, 55, 0.20) 0%,
                    rgba(212, 175, 55, 0.06) 36%,
                    rgba(212, 175, 55, 0) 72%
                );
            opacity: 0.7;
        }

        #lsgFloatingTools .lsg-floating-tools__item,
        #lsgFloatingTools button.lsg-floating-tools__item {
            position: relative;

            width: var(--lsg-ft-item-size);
            height: var(--lsg-ft-item-size);
            min-width: var(--lsg-ft-item-size);
            min-height: var(--lsg-ft-item-size);

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0;
            margin: 0;

            border: 1px solid var(--lsg-ft-item-border);
            outline: 0;
            border-radius: 5px;
            background: var(--lsg-ft-item-bg) !important;
            color: var(--lsg-ft-icon) !important;
            text-decoration: none !important;
            box-shadow: none;
            cursor: pointer;

            appearance: none;
            -webkit-appearance: none;

            transition:
                background-color 0.18s ease,
                border-color 0.18s ease,
                color 0.18s ease,
                transform 0.18s ease,
                box-shadow 0.18s ease,
                opacity 0.18s ease;
        }

        #lsgFloatingTools .lsg-floating-tools__item:active,
        #lsgFloatingTools .lsg-floating-tools__item:focus {
            color: var(--lsg-ft-icon) !important;
            text-decoration: none !important;
            outline: none;
        }

        #lsgFloatingTools .lsg-floating-tools__item:hover,
        #lsgFloatingTools .lsg-floating-tools__item:focus-visible {
            background: var(--lsg-ft-item-hover-bg) !important;
            border-color: var(--lsg-ft-item-border-hover);
            color: var(--lsg-ft-icon-hover) !important;
            text-decoration: none !important;
            transform: translateX(-2px) scale(1.04);
            box-shadow:
                0 8px 18px rgba(0, 0, 0, 0.14),
                0 0 0 1px rgba(212, 175, 55, 0.12);
            outline: none;
        }

        #lsgFloatingTools .lsg-floating-tools__item.is-active {
            background: var(--lsg-ft-item-active-bg) !important;
            border-color: var(--lsg-ft-item-border-hover);
            color: var(--lsg-ft-icon-hover) !important;
        }

        #lsgFloatingTools .lsg-floating-tools__item.is-active-click {
            transform: scale(0.95);
            opacity: 0.85;
        }

        body.theme-dark #lsgFloatingTools .lsg-floating-tools__item[data-theme-toggle],
        body[data-theme="dark"] #lsgFloatingTools .lsg-floating-tools__item[data-theme-toggle] {
            background: var(--lsg-ft-item-active-bg) !important;
            border-color: var(--lsg-ft-item-border-hover);
            color: var(--lsg-ft-icon-hover) !important;
        }

        #lsgFloatingTools .lsg-floating-tools__item i {
            font-size: 1.05rem;
            line-height: 1;
            pointer-events: none;
            color: inherit !important;
        }

        #lsgFloatingTools .lsg-floating-tools__notification-dot {
            position: absolute;
            top: -4px;
            left: -4px;

            width: 11px;
            height: 11px;
            min-width: 11px;
            min-height: 11px;

            display: inline-block;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, 0.88);
            background: var(--lsg-ft-badge-bg);
            box-shadow:
                0 4px 10px rgba(0, 0, 0, 0.24),
                0 0 0 4px rgba(239, 68, 68, 0.16);
            animation: lsgNotificationPulse 1.8s ease-in-out infinite;
        }

        @keyframes lsgNotificationPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow:
                    0 4px 10px rgba(0, 0, 0, 0.24),
                    0 0 0 4px rgba(239, 68, 68, 0.16);
            }
            50% {
                transform: scale(1.14);
                box-shadow:
                    0 5px 12px rgba(0, 0, 0, 0.28),
                    0 0 0 7px rgba(239, 68, 68, 0.08);
            }
        }

        .tooltip .tooltip-inner {
            border-radius: 5px;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 600;
        }

        @media (max-width: 1199.98px) {
            #lsgFloatingTools {
                right: 12px;
            }
        }

        @media (max-width: 991.98px) {
            #lsgFloatingTools {
                width: 54px;
                padding: 10px 6px;
            }

            #lsgFloatingTools .lsg-floating-tools__item {
                width: 38px;
                height: 38px;
                min-width: 38px;
                min-height: 38px;
                border-radius: 5px;
            }

            #lsgFloatingTools .lsg-floating-tools__item i {
                font-size: 0.95rem;
            }
        }

        @media (max-width: 767.98px) {
            #lsgFloatingTools {
                left: 12px;
                right: 12px;
                top: auto;
                bottom: calc(12px + env(safe-area-inset-bottom, 0px));
                transform: none;

                width: auto;
                max-width: none;
                max-height: none;
                min-height: 68px;
                padding: 10px 12px;

                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 8px;

                border-radius: 5px;
                box-shadow:
                    0 18px 40px rgba(0, 0, 0, 0.26),
                    0 8px 18px rgba(0, 0, 0, 0.16),
                    0 0 0 1px rgba(212, 175, 55, 0.18);
            }

            #lsgFloatingTools:hover {
                box-shadow:
                    0 18px 40px rgba(0, 0, 0, 0.26),
                    0 8px 18px rgba(0, 0, 0, 0.16),
                    0 0 0 1px rgba(212, 175, 55, 0.18);
            }

            #lsgFloatingTools .lsg-floating-tools__item {
                flex: 1 1 0;
                width: auto;
                min-width: 44px;
                max-width: none;
                height: 44px;
                min-height: 44px;
                border-radius: 5px;
                transform: none !important;
            }

            #lsgFloatingTools .lsg-floating-tools__item:hover,
            #lsgFloatingTools .lsg-floating-tools__item:focus-visible {
                transform: none !important;
                box-shadow:
                    0 0 0 1px rgba(212, 175, 55, 0.12);
            }

            #lsgFloatingTools .lsg-floating-tools__item i {
                font-size: 1rem;
            }

            #lsgFloatingTools .lsg-floating-tools__notification-dot {
                top: 4px;
                left: 9px;
            }

            body {
                padding-bottom: 92px;
            }
        }

        @media (max-width: 420px) {
            #lsgFloatingTools {
                left: 8px;
                right: 8px;
                bottom: calc(8px + env(safe-area-inset-bottom, 0px));
                padding: 8px 10px;
                min-height: 5px;
                border-radius: 5px;
            }

            #lsgFloatingTools .lsg-floating-tools__item {
                height: 42px;
                min-height: 42px;
                border-radius: 5px;
            }

            #lsgFloatingTools .lsg-floating-tools__item i {
                font-size: 0.95rem;
            }

            body {
                padding-bottom: 88px;
            }
        }

        @media (min-width: 768px) {
            #lsgFloatingTools {
                transition: transform .18s ease, opacity .18s ease;
            }

            #lsgFloatingTools:not(:hover):not(:focus-within) {
                transform: translateX(calc(100% - 20px));
                opacity: 1;
            }

            #lsgFloatingTools:hover,
            #lsgFloatingTools:focus-within {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 767.98px) {
            #lsgFloatingTools {
                transform: none !important;
                opacity: 1 !important;
            }
        }
</style>

    <button type="button"
            class="lsg-floating-tools__item"
            data-key="notifications"
            data-url="{{ route('notifications.index') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Notifications"
            aria-label="Notifications">
        <i class="fa-regular fa-bell"></i>

        @if($floatingNotificationsCount > 0)
            <span class="lsg-floating-tools__notification-dot"
                  title="{{ $floatingNotificationsCount }} notificações por ler"
                  aria-hidden="true"></span>
        @endif
    </button>
    <button type="button"
            class="lsg-floating-tools__item"
            data-url="{{ route('settings.index') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Settings"
            aria-label="Settings">
        <i class="fa-solid fa-gear"></i>
    </button>

    <button type="button"
            class="lsg-floating-tools__item"
            data-theme-toggle
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Alternar tema"
            aria-label="Alternar tema">
        <i class="fa-solid fa-circle-half-stroke"></i>
    </button>

    <button type="button"
            class="lsg-floating-tools__item"
            data-url="{{ route('shortcuts.index') }}"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Shortcuts"
            aria-label="Shortcuts">
        <i class="fa-solid fa-grip"></i>
    </button>

    <button type="button"
            class="lsg-floating-tools__item"
            data-submit-form="lsgFloatingLogoutForm"
            data-bs-toggle="tooltip"
            data-bs-placement="left"
            title="Logout"
            aria-label="Logout">
        <i class="fa-solid fa-right-from-bracket"></i>
    </button>

    <form id="lsgFloatingLogoutForm" method="POST" action="{{ route('logout') }}" class="d-none">
        @csrf
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initFloatingTooltips();
            markFloatingActiveItem();
            enhanceFloatingUX();
            bindFloatingActions();
        });

        function initFloatingTooltips() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
                return;
            }

            const elements = document.querySelectorAll('#lsgFloatingTools [data-bs-toggle="tooltip"]');

            elements.forEach(function (el) {
                new bootstrap.Tooltip(el, {
                    trigger: 'hover focus',
                    boundary: 'window'
                });
            });
        }

        function markFloatingActiveItem() {
            const currentUrl = normalizeUrl(window.location.href);
            const items = document.querySelectorAll('#lsgFloatingTools .lsg-floating-tools__item[data-url]');

            items.forEach(function (item) {
                const url = item.getAttribute('data-url');

                if (!url) {
                    return;
                }

                const itemUrl = normalizeUrl(url);

                if (currentUrl === itemUrl || currentUrl.startsWith(itemUrl + '/')) {
                    item.classList.add('is-active');
                }
            });
        }

        function normalizeUrl(url) {
            try {
                const u = new URL(url, window.location.origin);
                let path = u.pathname.replace(/\/+$/, '');
                return path === '' ? '/' : path;
            } catch (e) {
                return url;
            }
        }

        function enhanceFloatingUX() {
            const container = document.getElementById('lsgFloatingTools');

            if (!container) {
                return;
            }

            container.addEventListener('click', function (e) {
                const item = e.target.closest('.lsg-floating-tools__item');

                if (!item) {
                    return;
                }

                item.classList.add('is-active-click');

                setTimeout(function () {
                    item.classList.remove('is-active-click');
                }, 180);
            });

            container.addEventListener('touchstart', function () {}, { passive: true });
        }

        function bindFloatingActions() {
            const container = document.getElementById('lsgFloatingTools');

            if (!container) {
                return;
            }

            container.addEventListener('click', function (e) {
                const item = e.target.closest('.lsg-floating-tools__item');

                if (!item) {
                    return;
                }

                const submitFormId = item.getAttribute('data-submit-form');
                if (submitFormId) {
                    const form = document.getElementById(submitFormId);
                    if (form) {
                        form.submit();
                    }
                    return;
                }

                if (item.hasAttribute('data-theme-toggle')) {
                    return;
                }

                const url = item.getAttribute('data-url');
                if (url) {
                    window.location.href = url;
                }
            });
        }

        window.addEventListener('lsg:update-floating', function (e) {
            if (!e.detail) {
                return;
            }

            if (typeof e.detail.notifications !== 'undefined') {
                updateNotificationBadge(e.detail.notifications);
            }
        });

        function updateNotificationBadge(count) {
            const button = document.querySelector('#lsgFloatingTools [data-key="notifications"]');
            if (!button) {
                return;
            }

            count = parseInt(count || 0, 10);
            let dot = button.querySelector('.lsg-floating-tools__notification-dot');

            if (count <= 0) {
                if (dot) {
                    dot.remove();
                }
                return;
            }

            if (!dot) {
                dot = document.createElement('span');
                dot.className = 'lsg-floating-tools__notification-dot';
                dot.setAttribute('aria-hidden', 'true');
                button.appendChild(dot);
            }

            dot.setAttribute('title', count + ' notificações por ler');
        }
    </script>
</div>
