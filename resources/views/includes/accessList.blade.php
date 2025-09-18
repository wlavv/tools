@if( isset($accessList) && ( count($accessList) > 0))
    <div class="navbar navbar-light customPanel" style="border-radius: 5px 5px 0 0">
        <div class="listUL" style="margin: 0 auto;display: table; text-transform: uppercase;font-weight: bolder;"> 
            @foreach($accessList AS $access)
                <div class="rowStyling" style="width: 120px; height: 120px; text-align: center; float: left;border: 1px solid #ccc;padding: 20px 10px;margin: 0;"> 
                    <a href="{{$access['url']}}"> 
                        <div>
                            @if( !is_null($access['icon']))    
                                <i class="fa-regular {!! $access['icon'] !!}" style="font-size: 45px;margin-bottom: 10px;"></i>
                            @else
                                {!! $access['image'] !!}
                            @endif
                        </div>
                        <div style="padding-top: 5px;">{{ $access['name']}}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif