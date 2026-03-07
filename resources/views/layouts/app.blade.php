<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WebTools') }}</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{asset('admin/css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css/dropzone.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('admin/css/app.css')}}?t={{rand()}}">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('admin/js/sweetalert2.min.js')}}"></script>
    <script src="{{asset('admin/js/dropzone.min.js')}}"></script>

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @include('includes.js')
</head>
<body>
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
                        <aside id="mainMenuMobileContainer" class="app-sidebar">
                            <div class="app-sidebar-inner">
                                @include('includes.mobileMenu')
                            </div>
                        </aside>

                        <section class="app-main">
                            <header class="app-topbar top_container">
                                <div class="app-topbar-inner {{ $hasTopbarActions ? 'has-topbar-actions' : 'no-topbar-actions' }}">
                                    <div class="topbar-left">
                                        <div id="breadcrumbs" class="breadcrumbs-card">
                                            @include('includes.breadcrumbs')
                                        </div>
                                    </div>

                                    @if($hasTopbarActions)
                                        <div class="topbar-right">
                                            <div id="extraMenu" class="quick-toolbar quick-toolbar-actions">
                                                @include('includes.action')
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </header>

                            <div id="mainContentView" class="app-content">
                                <div class="page-content-stack">
                                @if(isset($accessList))
                                    @include('includes.accessList', $accessList)
                                @endif

                                @if(isset($counters))
                                    <div class="row g-3 dashboard-counters-row">
                                        @foreach($counters AS $counter)
                                            @include('includes.counters', $counter)
                                        @endforeach
                                    </div>
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
</body>
</html>
