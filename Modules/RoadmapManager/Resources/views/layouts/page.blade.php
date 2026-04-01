@extends(config('roadmap-manager.layout', 'layouts.app'))

@section('content')
<div class="roadmap-manager-page">
    @include('roadmap-manager::partials.alerts')
    @yield('roadmap-content')
</div>
@endsection