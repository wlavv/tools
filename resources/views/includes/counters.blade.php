<div @if(isset($col)) class="col-lg-{{$col}} col-md-6 col-12" @else class="col-lg-2 col-md-4 col-12" @endif>
    <div class="customPanel counter-panel">
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                @php
                    $counterClass = isset($info) ? 'counter-box-info' : (($counter > 0) ? 'counter-box-alert' : 'counter-box-ok');
                @endphp
                <div class="counter-box {{$counterClass}} @if($counter < 1) counter-box-disabled @endif"
                     onclick="@if(!isset($direct_link)) $('.{{$item_id}}').toggle() @else window.open('{{$direct_link}}', '_blank') @endif">
                    <div class="counter-value" id="{{$item_id}}_quantity">{{$counter}}</div>
                    <div class="counter-label">{{$name}}</div>
                </div>
            </div>
            <div class="panel-body counter-table-wrap @if($counter > 0) {{$item_id}} @endif" style="display: none; overflow-x: auto;">
                <table class="table table-bordered customTable text-center premium-table">
                    <tr class="table-head-row">
                        @foreach($columns AS $column)
                            <td>@if($column == 'clean') @else {{ __('tags.' . $column) }} @endif</td>
                        @endforeach
                    </tr>
                    @foreach($data AS $item)
                        @if(isset($exception_fields))
                        <tr id="row_exception_{{$item[$exception_fields[1]]}}">
                        @else
                        <tr>
                        @endif
                            @foreach($columns AS $column)
                                @if($column == 'extra_action')
                                    <td>{!! $item[$column] !!}</td>
                                @elseif($column == 'clean')
                                    <td class="{{$column}}">
                                        <img src="/admin/images/check.png" onclick="setRowAsChecked( '{{$exception_fields[0]}}', '{{$item[$exception_fields[1]]}}', '{{$item[$exception_fields[2]]}}', '{{$item[$exception_fields[3]]}}', '{{$item_id}}' )">
                                    </td>
                                @elseif($column == 'delete')
                                    <td id="delete_{{$item['delete']}}"><i class="fa-solid fa-trash" onclick="deleteItem({{$item['delete']}}, '{{$table}}')"></i></td>
                                @elseif($column == 'send_email')
                                    <td id="send_email_{{$item['send_email']}}"><i class="fa-solid fa-envelope" onclick="requestedProductSendEmail('{{$item['send_email']}}')"></i></td>
                                @else
                                    <td class="{{$column}}" @if(isset($prestashop)) onclick="window.open('https://all-stars-motorsport.com/admin77500/index.php?controller={{$prestashop['controller']}}&{{$prestashop['element']}}={{$item[$prestashop['element']]}}&token={{$prestashop['token']}}{{$prestashop['extraParameters']}}', '_blank')" style="cursor: pointer;" @endif>
                                        {!! $item[$column] !!}
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
