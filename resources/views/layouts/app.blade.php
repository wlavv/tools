<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WebTools') }}</title>

    <script>
        (function () {
            var storageKey = 'webtools-theme';
            var theme = 'dark';

            try {
                theme = localStorage.getItem(storageKey) === 'light' ? 'light' : 'dark';
            } catch (e) {
                theme = 'dark';
            }

            var root = document.documentElement;
            root.setAttribute('data-theme', theme);
            root.setAttribute('data-bs-theme', theme);
            root.classList.remove('theme-dark', 'theme-light');
            root.classList.add('theme-' + theme);
            root.style.colorScheme = theme;
            window.__LSG_INITIAL_THEME__ = theme;
        })();
    </script>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/lsg-select2.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('admin/css/sweetalert2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/dropzone.min.css') }}"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="{{ asset('admin/css/app.css') }}?t={{ rand() }}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('admin/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('admin/js/dropzone.min.js') }}"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @auth
        @include('documentmanager::Includes.css')
    @endauth
    @stack('styles')
    <style>
        .app-shell {
            --lsg-bo-radius: var(--radius-md, 0);
            --dms-radius: var(--lsg-bo-radius);
            --pm-radius: var(--lsg-bo-radius);
            --catalog-radius: var(--lsg-bo-radius);
            --wc-radius: var(--lsg-bo-radius);
            --erp-radius: var(--lsg-bo-radius);
            --mh-radius: var(--lsg-bo-radius);
            --pm-border-radius: var(--lsg-bo-radius);
        }

        .app-shell :is(
            .card,
            .btn,
            .badge,
            .alert,
            .modal-content,
            .dropdown-menu,
            .form-control,
            .form-select,
            .input-group-text,
            .page-link,
            .table-responsive,
            .breadcrumbs-card,
            .quick-toolbar,
            .customPanel,
            .lsg-action-btn,
            .lsg-action-btn__glow,
            .sidebar-brand-link,
            .sidebar-brand-logo,
            .sidebar-nav-item,
            .sidebar-nav-icon,
            .quick-access-link,
            .quick-access-icon,
            .counter-box,
            .language-switcher-current,
            .language-switcher-menu,
            .select2-container .select2-selection,
            .dataTables_wrapper input,
            .dataTables_wrapper select,
            [class*="-card"],
            [class*="__card"],
            [class*="-panel"],
            [class*="__panel"],
            [class*="-pill"],
            [class*="__pill"],
            [class*="-badge"],
            [class*="__badge"],
            [class*="-chip"],
            [class*="__chip"],
            [class*="-icon"],
            [class*="__icon"],
            [class*="-btn"],
            [class*="__btn"],
            input,
            select,
            textarea,
            summary,
            details,
            table,
            th,
            td
        ) {
            border-radius: var(--lsg-bo-radius) !important;
        }

        .app-shell {
            --lsg-final-btn-bg: var(--lsg-bo-btn-bg, #405061);
            --lsg-final-btn-bg-hover: var(--lsg-bo-btn-bg-hover, #536579);
            --lsg-final-btn-border: var(--lsg-bo-btn-border, var(--border-soft, rgba(255, 255, 255, .12)));
            --lsg-final-btn-border-hover: var(--lsg-bo-btn-border-hover, rgba(142, 164, 255, .38));
            --lsg-final-btn-text: var(--lsg-bo-btn-text, #f8fafc);
            --lsg-final-btn-shadow: var(--lsg-bo-btn-shadow, 0 8px 18px rgba(0, 0, 0, .12));
        }

        .app-shell :is(
            .btn,
            .lsg-action-btn,
            .pm-btn,
            .password-manager-btn,
            .wc-btn,
            .task-toggle-btn,
            .calendar-member-tab,
            button[class*="-btn"],
            a[class*="-btn"],
            button[class*="__btn"],
            a[class*="__btn"],
            input[type="submit"]
        ):not(.btn-close) {
            background: var(--lsg-final-btn-bg) !important;
            background-image: none !important;
            border: 1px solid var(--lsg-final-btn-border) !important;
            color: var(--lsg-final-btn-text) !important;
            box-shadow: var(--lsg-final-btn-shadow) !important;
            filter: none !important;
            text-shadow: none !important;
        }

        .app-shell :is(
            .btn,
            .lsg-action-btn,
            .pm-btn,
            .password-manager-btn,
            .wc-btn,
            .task-toggle-btn,
            .calendar-member-tab,
            button[class*="-btn"],
            a[class*="-btn"],
            button[class*="__btn"],
            a[class*="__btn"],
            input[type="submit"]
        ):not(.btn-close):hover,
        .app-shell :is(
            .btn,
            .lsg-action-btn,
            .pm-btn,
            .password-manager-btn,
            .wc-btn,
            .task-toggle-btn,
            .calendar-member-tab,
            button[class*="-btn"],
            a[class*="-btn"],
            button[class*="__btn"],
            a[class*="__btn"],
            input[type="submit"]
        ):not(.btn-close):focus {
            background: var(--lsg-final-btn-bg-hover) !important;
            border-color: var(--lsg-final-btn-border-hover) !important;
            color: var(--lsg-final-btn-text) !important;
        }

        .app-shell :is(
            .btn-primary,
            .btn-outline-primary,
            .btn-info,
            .btn-outline-info,
            .lsg-action-btn--primary,
            .lsg-action-btn--back,
            .lsg-action-btn--gold,
            .btn-action,
            .pm-btn--primary,
            .wc-btn--primary
        ):not(.btn-close) {
            background: var(--lsg-bo-btn-primary-bg, #1d4ed8) !important;
            border-color: var(--lsg-bo-btn-primary-border, rgba(96, 165, 250, .76)) !important;
            color: var(--lsg-bo-btn-primary-text, #eff6ff) !important;
        }

        .app-shell :is(
            .btn-success,
            .btn-outline-success,
            .lsg-action-btn--success,
            .pm-btn--success,
            .wc-btn--success,
            .password-manager-btn-primary,
            .task-toggle-btn.is-success.active
        ):not(.btn-close) {
            background: var(--lsg-bo-btn-success-bg, #166534) !important;
            border-color: var(--lsg-bo-btn-success-border, rgba(74, 222, 128, .72)) !important;
            color: var(--lsg-bo-btn-success-text, #f0fdf4) !important;
        }

        .app-shell :is(
            .btn-warning,
            .btn-outline-warning,
            .lsg-action-btn--warning,
            .pm-btn--warning,
            .wc-btn--warning
        ):not(.btn-close) {
            background: var(--lsg-bo-btn-warning-bg, #92400e) !important;
            border-color: var(--lsg-bo-btn-warning-border, rgba(251, 191, 36, .72)) !important;
            color: var(--lsg-bo-btn-warning-text, #fffbeb) !important;
        }

        .app-shell :is(
            .btn-danger,
            .btn-outline-danger,
            .lsg-action-btn--danger,
            .pm-btn--danger,
            .wc-btn--danger,
            .task-toggle-btn.is-danger.active
        ):not(.btn-close) {
            background: var(--lsg-bo-btn-danger-bg, #991b1b) !important;
            border-color: var(--lsg-bo-btn-danger-border, rgba(248, 113, 113, .72)) !important;
            color: var(--lsg-bo-btn-danger-text, #fff1f2) !important;
        }

        .app-shell :is(.btn i, .lsg-action-btn i, .pm-btn i, .password-manager-btn i, .wc-btn i, .task-toggle-btn i) {
            color: inherit !important;
        }
    </style>

</head>
<body data-theme="dark" data-bs-theme="dark" class="theme-dark">
    <script>
        (function () {
            var theme = window.__LSG_INITIAL_THEME__ === 'light' ? 'light' : 'dark';
            var body = document.body;

            body.setAttribute('data-theme', theme);
            body.setAttribute('data-bs-theme', theme);
            body.classList.remove('theme-dark', 'theme-light');
            body.classList.add('theme-' + theme);
            body.style.colorScheme = theme;
        })();
    </script>
    <div id="app">
        <main>
            @guest
                <div class="guest-shell">
                    @yield('content')
                </div>
            @else
                @auth
                    @php
                        $hasTopbarActions = isset($actions) && is_array($actions) && count($actions) > 0;
                    @endphp
                    <div class="app-shell">
                        <div class="app-sidebar-backdrop" data-mobile-menu-close></div>

                        <aside id="mainMenuMobileContainer" class="app-sidebar">
                            <div class="app-sidebar-inner">
                                @include('includes.menu')
                            </div>
                        </aside>

                        <section class="app-main">

                            <header class="app-topbar top_container">
                                <div class="app-topbar-inner {{ $hasTopbarActions ? 'has-topbar-actions' : 'no-topbar-actions' }}">
                                    <div class="topbar-left">
                                        <div id="breadcrumbs" class="breadcrumbs-card {{ $hasTopbarActions ? 'has-breadcrumb-actions' : '' }}">
                                            <div class="breadcrumbs-card-main">
                                                @include('includes.breadcrumbs')
                                            </div>

                                            @if($hasTopbarActions)
                                                <div id="extraMenu" class="breadcrumbs-actions">
                                                    @include('includes.action')
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </header>

                            <div id="mainContentView" class="app-content">
                                <div class="page-content-stack">
                                    @if(isset($accessList))
                                        @include('includes.accessList', $accessList)
                                    @endif


                                    @yield('content')
                                </div>
                            </div>
                        </section>
                    </div>
                @endauth
            @endguest
        </main>
    </div>
    @include('includes.floating-tools')
    @auth
        @include('documentmanager::partials.quick-upload', [
            'modal' => true,
            'showButton' => false,
            'uploadId' => 'globalDocumentUpload',
            'buttonLabel' => 'Documento',
        ])
        @include('documentmanager::Includes.js')
    @endauth
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/lsg-select2.js') }}"></script>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                return;
            }

            jQuery('.lsg-datatable, .catalog-lsg-datatable').each(function () {
                var table = jQuery(this);
                var columnCount = table.find('thead th').length;
                var hasInvalidBodyRows = false;

                if (jQuery.fn.DataTable.isDataTable(this)) {
                    return;
                }

                table.find('tbody tr').each(function () {
                    var cells = jQuery(this).children('td, th');

                    if (cells.filter('[colspan]').length || cells.length !== columnCount) {
                        hasInvalidBodyRows = true;
                    }
                });

                if (!columnCount || hasInvalidBodyRows) {
                    return;
                }

                table.DataTable({
                    pageLength: parseInt(table.data('page-length') || '25', 10),
                    order: [],
                    autoWidth: false,
                    language: {
                        search: 'Pesquisar:',
                        lengthMenu: 'Mostrar _MENU_ registos',
                        info: 'A mostrar _START_ a _END_ de _TOTAL_ registos',
                        infoEmpty: 'Sem registos',
                        infoFiltered: '(filtrado de _MAX_ registos)',
                        zeroRecords: 'Nenhum registo encontrado',
                        emptyTable: 'Sem dados disponiveis',
                        paginate: {
                            first: 'Primeiro',
                            previous: 'Anterior',
                            next: 'Seguinte',
                            last: 'Ultimo'
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
