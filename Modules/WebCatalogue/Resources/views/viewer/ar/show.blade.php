@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell"><div class="wc-card"><h3>AR Viewer · <span class="wc-html-inline">{!! $product->name !!}</span></h3><p class="wc-muted">AR foundation view.</p></div></div>
@endsection
