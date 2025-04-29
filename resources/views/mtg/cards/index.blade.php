@extends('layouts.app')

@section('content')

<div class="navbar navbar-light customPanel">
{{count($cards)}}
    <div class="row">    
        @foreach($cards AS $card)
            <div class="col-lg-2" style="text-align: center;">
                <div style="height: 405px;margin: 10px 0; background-color: #dedede; border: 1px solid #666;border-radius: 5px;cursor: pointer;">
                    <div style="height: 330px;display: flex;padding: 10px;">
                        <img src="/images/mtg/{{$card->set_code}}/{{$card->collector_number}}.jpg" style="width: 250px;margin: 0 auto;vertical-align: middle;border-radius: 5px;">
                    </div>
                    <div style="text-align: center;margin: 5px 0;">{{$card->name}}</div>
                    <div class="card-name {{$card->rarity}}" style="background-color: #FFF;height: 40px;border-radius: 0 0 5px 5px;">
                        <span style="width: calc( 60% - 40px ); padding: 3px;float:left;text-align: left; margin: 10px 0;">{{$card->rarity}}</span>
                        <img style="width: 40px; float: left;padding: 5px; margin: 0 auto;" src="{{$set->icon_svg_uri}}">
                        <span style="padding: 3px;float: right;color: #000; margin: 10px 0;">{{$card->collector_number}}</span>
                    </div>
                </div>
            </div>
        @endforeach
        
    </div>
</div>

<style>

.card-name.common { color: #6c757d; }

.card-name.uncommon { color: #007bff; }

.card-name.rare { color: #ffc107; }

.card-name.mythic { color: #dc3545; }

.card-name {  font-weight: bold; text-align: center; text-transform: uppercase; }

</style>
@endsection