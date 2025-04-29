@extends('layouts.app')

@section('content')

<div class="navbar navbar-light customPanel">
    <div class="row">    
        @foreach($sets AS $set)
            <div class="col-lg-2" style="text-align: center;">
                <div style="height: 190px;margin: 10px 0; background-color: #dedede; border: 1px solid #666;padding: 10px;border-radius: 5px;cursor: pointer;">
                    <a href="{{route('mtg.showSet', $set->set_code)}}" style="text-decoration: none;">
                        <div style="height: 130px;display: flex;">
                            @if(isset($set->icon_svg_uri))
                            <img src="{{$set->icon_svg_uri}}" style="width: 100px;margin: 0 auto;vertical-align: middle;">
                            @endif
                        </div>
                        <div style="text-align: center;"><span style="text-transform: uppercase;color: dodgerblue;font-weight: bolder;">{{$set->set_code}} </span> | <span>{{$set->set_name}}</span></div>
                    </a>
                </div>
            </div>
        @endforeach
        
    </div>
</div>

@endsection