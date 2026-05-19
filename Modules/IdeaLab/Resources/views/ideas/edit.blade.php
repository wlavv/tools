@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')
<form id="lsg-form" method="POST" action="{{ route('idealab.update', $idea) }}">
    @csrf
    @method('PUT')
    @include('idealab::ideas._form')
</form>
@endsection
