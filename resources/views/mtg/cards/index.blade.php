@extends('layouts.app')

@section('content')



<style>
    .counter{ font-size: 35px; vertical-align: middle; padding-left: 10px;}

    .color_filter{ float: left; width: 12%; border: 1px solid #dedede;text-align: center; padding: 10px;background-color: #ddd; }
</style>
<div class="navbar navbar-light customPanel" style="display: flex;">
    <div class="color_filter">
        <div class="counter-box colored">
            <img src="/images/mtg/mana/mtg.png" style="width: 70px;">
            <span class="counter">{{$card_counters->all}}</span>
        </div>
    </div>
    <div class="color_filter">
        <div class="counter-box">
            <img src="/images/mtg/mana/white.svg" style="width: 70px;">
            <span class="counter">{{$card_counters->white}}</span>
        </div>
    </div>
    <div class="color_filter">
        <div class="counter-box">
            <img src="/images/mtg/mana/blue.svg" style="width: 70px;">
            <span class="counter">{{$card_counters->blue}}</span>
        </div>
    </div>
    <div class="color_filter">
        <div class="counter-box">
            <img src="/images/mtg/mana/black.svg" style="width: 70px;">
            <span class="counter">{{$card_counters->black}}</span>
        </div>
    </div>
    <div class="color_filter">
        <div class="counter-box">
            <img src="/images/mtg/mana/red.svg" style="width: 70px;">
            <span class="counter">{{$card_counters->red}}</span>
        </div>
    </div>
    <div class="color_filter">
        <div class="counter-box">
            <img src="/images/mtg/mana/green.svg" style="width: 70px;">
            <span class="counter">{{$card_counters->green}}</span>
        </div>
    </div>
    <div class="color_filter">
        <div class="counter-box">
            <img src="/images/mtg/mana/colorless.svg" style="width: 70px;">
            <span class="counter">{{$card_counters->colorless}}</span>
        </div>
    </div>
    <div class="color_filter">
        <div class="counter-box">
            <img src="/images/mtg/mana/multicolor.webp" style="width: 70px;">
            <span class="counter">{{$card_counters->multicolor}}</span>
        </div>
    </div>
</div>

<div class="navbar navbar-light customPanel">
    <div class="row">    
        @foreach($cards AS $card)
            <div class="col-lg-2" style="text-align: center;">
                <div style="height: 430px;margin: 10px 0; background-color: #dedede; border: 1px solid #666;border-radius: 5px;cursor: pointer;">
                    <div style="height: 330px;display: flex;padding: 10px;">
                        <img src="/images/mtg/{{$card->set_code}}/{{$card->collector_number}}.jpg" style="width: 250px;margin: 0 auto;vertical-align: middle;border-radius: 5px;">
                    </div>
                    <div style="text-align: center;margin: 5px 0;height: 48px;">{{$card->name}}</div>
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

.card-name.common {     color: #6c757d; }
.card-name.uncommon {   color: #007bff; }
.card-name.rare {       color: #ffc107; }
.card-name.mythic {     color: #dc3545; }

.card-name {  font-weight: bold; text-align: center; text-transform: uppercase; }

</style>
@endsection