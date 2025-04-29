<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'WebTools') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="{{asset('admin/css/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('admin/css/dropzone.min.css')}}"/>
    <link rel="stylesheet" href="{{asset('admin/css/app.css')}}?t={{rand()}}">
    <!-- Scripts -->

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
                <div style="margin: 60px 0">
                    @yield('content')
                </div>
            @else
                @auth
                    <div class="mainContainer">
                        <div class="navbar navbar-light shadow-sm top_container">
                            <div style="display: contents;">
                                <div style="width: 100%; background-color: #ccc; border-bottom: 1px solid #999;">
                                    <div id="extraMenu" class="text-center" style="height: auto; margin-bottom: 10px;height: 30px; float: right;display:none;"> 
                                        <div style="border: 1px solid #bbb;margin-right: 10px;">
                                            <div class="input-group">
                                                <input id="extraSearchInput" type="text" class="form-control" aria-label="PRODUCT ID, REFERENCE OR EAN-13" aria-describedby="basic-addon2" placeholder="PRODUCT ID, REFERENCE OR EAN-13" style="width: 300px;border-radius: initial;">
                                                    <div class="input-group-append">
                                                    <button onclick="cleanGlobalFilter()" class="btn btn-warning" type="button" style="border-radius: initial;"><i class="fa-solid fa-eraser" style="font-size: 20px;"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="languageSelector" style="text-transform: uppercase; text-align: center; width: 100%; height: 35px; float: right; display: none;;" onclick="$('#languageSelector').toggle();$('#languageSelectorContainer').toggle();">
                                            <img style="width: 25px; border-radius: 25px; margin: 7px 5px; float: right; border: 1px solid #999;" src="/images/flags/{{app()->getLocale()}}.png">
                                        </div>
                                        <div id="languageSelectorContainer" style="display: none;width: 100%; height: 35px;">
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/en"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/en.png"> </a>
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/es"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/es.png"> </a>
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/fr"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/fr.png"> </a>
                                            <a style="width: 25%; float: left; text-align: center;" class="nav-link uppercase" href="/language/pt"> <img style="width: 25px; border-radius: 25px; margin: 7px 5px; border: 1px solid #999;" src="/images/flags/pt.png"> </a>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin: 10px;width: 100%;">
                                    <div style="width: 65%; float: left;"  id="breadcrumbs">  @include('includes.breadcrumbs') </div>
                                </div>
                            </div>
                        </div>
                        <div id="mainMenuMobileContainer">
                            <div id="mainMenuMobile"> @include('includes.mobileMenu') </div>
                        </div>
                        <div id="mainContentView" style="width: calc( 100% - 170px); float: right;margin-top: 105px;">
                            @if( isset( $accessList) )
                                @include("includes.accessList", $accessList)
                            @endif

                            @if( isset( $counters) )
                                <div class="row">
                                    @foreach( $counters AS $counter)
                                        @include("includes.counters", $counter)
                                    @endforeach
                                </div>
                            @endif

                            @yield('content') 
                        </div>
                    </div>
                @endauth
            @endguest
        </main>
    </div>
</body>
</html>
