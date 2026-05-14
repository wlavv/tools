@extends(config('streamdeck-access.layout', 'layouts.app'))

@section('content')
    @include('streamdeck-access::Includes.css')

    <div class="streamdeck-access-shell streamdeck-access-shell--form">
        @if($errors->any())
            <div class="streamdeck-access-alert streamdeck-access-alert--warning">
                Existem erros no formulário. Revê os campos assinalados.
            </div>
        @endif

        @include('streamdeck-access::Includes._components.form', [
            'accessPoint' => $accessPoint,
            'action' => $action,
            'method' => $method,
        ])
    </div>

    @include('streamdeck-access::Includes.js')
@endsection
