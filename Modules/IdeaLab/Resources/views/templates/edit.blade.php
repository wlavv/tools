@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')
<form id="lsg-form" method="POST" action="{{ route('idealab.templates.update', $template) }}">
    @csrf
    @method('PUT')
    @include('idealab::templates._form')
</form>
@endsection
