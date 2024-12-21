@extends('layouts.app')
@section('content')

<div class="navbar navbar-light customPanel">
    <div class="listUL" style="margin: 0 auto;display: table;">
        @foreach($accessList AS $access)
        <div class="rowStyling" style="width: 100px; height: 100px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;"> 
            <a href="{{$access['url']}}">     
                <div>{!! $access['icon'] !!}</div>
                <div style="line-height: 18px;">{{ $access['name']}}</div>
            </a>
        </div>
        @endforeach
    </div>
</div>

    <div class="navbar navbar-light customPanel">
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.id')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->id_manufacturer}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.name')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->name}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.warranty')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->warranty}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.countryCode')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->country_code}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.currency')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->currency}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.creation_date')}}:</label> </div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->date_add}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.update_date')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$manufacturer->date_upd}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.active')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">@if($manufacturer->active == 1) <span class="text-green">{{ __('messages.yes')}}</span> @else <span class="text-red">{{ __('messages.no')}}</span> @endif</label></div>
        </div>
    </div>

    @foreach($manufacturer->lang AS $lang)
    <div class="navbar navbar-light customPanel">
        <div class="row">
            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.lang')}}: </label></div>
            <div class="col-lg-10 dataContainer"><img src="/images/lang/{{$lang->id_lang}}.jpg" style="width: 30px"></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.description')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{!! $lang->description !!}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.short_description')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{!! $lang->short_description !!}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_title')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$lang->meta_title}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_keywords')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$lang->meta_keywords}}</label></div>

            <div class="col-lg-2 labelContainer"><label class="showInfo">{{ __('messages.meta_description')}}: </label></div>
            <div class="col-lg-10 dataContainer"><label class="showInfo">{{$lang->meta_description}}</label></div>
        </div>
    </div>
    @endforeach
@endsection