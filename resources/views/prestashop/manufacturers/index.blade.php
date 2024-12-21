@extends('layouts.app')
@section('content')

<div class="customPanel" style="background: none; padding:0;">
    <ul class="listUL" style="width: 100%;">
        @foreach ($manufacturers as $manufacturer)
            <li class="imagesListing" style="list-style: none;">
                <div class="rowStyling" style="margin: 5px;">
                    <a href="{{route('manufacturers.show', $manufacturer->id_manufacturer )}}" title="{{ $manufacturer->name }}">
                        <div>
                            <img src="https://asd.euromuscleparts.com/img/asd/150px/{{ $manufacturer->id_manufacturer }}.webp" style="width: 130px;"> 
                            <div>{{ $manufacturer->name }}</div>
                        </div>
                    </a>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@endsection