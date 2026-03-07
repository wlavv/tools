@if(isset($accessList) && (count($accessList) > 0))
    <div class="customPanel quick-access-panel">
        <div class="quick-access-grid">
            @foreach($accessList AS $access)
                <div class="quick-access-item">
                    <a href="{{$access['url']}}" class="quick-access-link">
                        <div class="quick-access-icon">
                            @if(!is_null($access['icon']))
                                <i class="fa-regular {!! $access['icon'] !!}"></i>
                            @else
                                {!! $access['image'] !!}
                            @endif
                        </div>
                        <div class="quick-access-title">{{ $access['name']}}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif
