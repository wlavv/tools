@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell"><div class="wc-card"><h3>VR Viewer · <span class="wc-html-inline">{!! $product->name !!}</span></h3><p class="wc-muted">VR foundation view.</p></div></div>
@endsection
