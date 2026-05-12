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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
    <script src="{{ asset('admin/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('admin/js/dropzone.min.js') }}"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @auth
        @include('documentmanager::Includes.css')
    @endauth
    @stack('styles')

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
