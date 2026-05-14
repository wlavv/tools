@extends(config('streamdeck-access.layout', 'layouts.app'))

@section('content')
    @include('streamdeck-access::Includes.css')

    <div class="streamdeck-access-shell">
        @if(session('success'))
            <div class="streamdeck-access-alert">{{ session('success') }}</div>
        @endif

        @include('streamdeck-access::Includes._components.stats', ['accessPoints' => $accessPoints])
        @include('streamdeck-access::Includes._components.toolbar', ['filters' => $filters])

        @include('streamdeck-access::Includes._components.table', ['accessPoints' => $accessPoints])
    </div>

    @include('streamdeck-access::Includes.js')
@endsection
