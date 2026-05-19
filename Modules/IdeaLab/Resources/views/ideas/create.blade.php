@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')
<form id="lsg-form" method="POST" action="{{ route('idealab.store') }}">
    @csrf
    @include('idealab::ideas._form')
</form>
@endsection
