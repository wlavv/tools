@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')
<form id="lsg-form" method="POST" action="{{ route('idealab.templates.store') }}">
    @csrf
    @include('idealab::templates._form')
</form>
@endsection
