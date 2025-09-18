
<div class="col-lg-4" style="">    
    <div class="navbar navbar-light customPanel" style="padding: 0;@if( $isMobile )margin-top: 0;@endif">
        <div style=" height: 50px;">
            <div style="text-align: left;cursor: pointer;width: calc(100% - 60px); vertical-align: middle;font-size: 25px;padding: 5px;float: left;" onclick="$('.{{$slug}}_Content').toggle()"><span style="display: block;width: 60px;float: left;text-align: center;font-size: 14px;margin-right: 10px;line-height: 2; background-color: {{(isset($spent[$slug])) ? $spent[$slug]['color'] : '#FFF'}}; border-radius: 5px; border: 1px solid #999; padding: 5px 0;">@if(isset($spent[$slug])){{number_format($spent[$slug]['detail']->percent, 0, ',', '')}} % @else 0 % @endif</span>{{$title}}</div>
            <div style="text-align: center;cursor: pointer; display: none;" onclick="alert('SHOW GRAPH')">
                <button class="btn btn-primary" style="color: #FFF;width: 40px;height: 40px;margin: 5px;;float: right;">
                    <i class="fa-solid fa-chart-simple"></i> 
                </button>
            </div>
        </div>
        <table class="table table-striped" style="width: 100%;text-align: center;margin-bottom: 0;display: table;">

            <tr class="header_totals_Content {{$slug}}_Content">
                <td style="color: white; background-color: dodgerblue; font-weight: bolder; text-align: right;">Rubrica</td>
                <td style="color: white; background-color: dodgerblue; font-weight: bolder; text-align: center;">Previsto</td>
                <td style="color: white; background-color: dodgerblue; font-weight: bolder; text-align: center;">Gasto</td>
                <td style="color: white; background-color: dodgerblue; font-weight: bolder; text-align: center;">%</td>
                <td style="color: white; background-color: dodgerblue; font-weight: bolder; text-align: center;">€</td>
            </tr>

            @if(isset($row) && isset($row->sons))

                @php
                    $total_forecast = 0;
                    $total_diff = 0;
                    $total_expenses = 0;
                @endphp
                
                @foreach($row->sons as $key => $item)

                    @php
                        
                        $expense = ( !is_null($item->expense) ) ? $item->expense : 0;
                        $diff_value = $expense - $item->forecast;
                        
                        if($item->forecast > 0){
                            $diff_percent = ( 1 - ( $expense / $item->forecast ) ) * -100;
                        }else{
                            $diff_percent = 0;
                        }
                        
                        $color = 'style="background-color: #d1e7dd; color: #0f5132; border-radius: 0;"';
                        
                        if( $diff_value > -0.00 ) $color = 'style="background-color: #f8d7da; color: #842029; border-radius: 0;"';
                        
                        if( ( $diff_value == 0.00 ) && ( $expense == 0) ) $color = 'style="background-color: #fff3cd; color: #664d03;"';
                        if( -$diff_value == $item->forecast ) $color = 'style="background-color: #fff3cd; color: #664d03;"';
                        
                        
                        $total_forecast += $item->forecast;
                        $total_diff += $diff_value;
                        $total_expenses += $expense;
                        
                        $color_total = 'style="background-color: #fff; color: #0f5132; border-radius: 0;font-weight: bolder;"';
                        
                        if( $total_diff > -0.00 ) $color_total = 'style="background-color: #fff; color: #842029; border-radius: 0;font-weight: bolder;"';
                        
                        if( ( $total_diff == 0.00 ) && ( $total_expenses == 0) ) $color_total = 'style="background-color: #fff; color: #664d03;font-weight: bolder;"';
                        if( -$total_diff == $total_forecast ) $color_total = 'style="background-color: #fff; color: #664d03;font-weight: bolder;"';
                        
                        if($item->forecast > 0){
                            $total_diff_percent = ( 1 - ( $total_expenses / $total_forecast ) ) * -100;
                        }else{
                            $total_diff_percent = 0;
                        }
                    
                    
                    @endphp
        
                    @if (!in_array($item->slug,['restaurante', 'supermercado', 'combustivel', 'auto', 'ferias', 'saude', 'extras', 'meninas', 'casa', 'ls', 'cuidadosPessoais', 'atividades'])) 

                        <tr class="{{$slug}}_Content">
                            <td {!!$color!!} class="row_color_{{$item->slug}} text-right">{{ $item->name }}</td>
                            <td {!!$color!!} class="row_color_{{$item->slug}}" style="width: 105px !important" onclick="updateForecastValue('{{$item->slug}}')">
                                {{ number_format($item->forecast, 2, ',', '.') }} €
                                <input type="hidden" value="{{$item->forecast}}" id="forecast_{{$slug}}_{{$item->slug}}"></inpput>
                            </td>
                        
                            <td {!!$color!!} class="row_color_{{$item->slug}}" onclick="updateValue('{{$item->slug}}', 'expense', '{{$slug}}')">
                                <span style="color: black; font-weight: bolder;cursor: pointer;" id="display_expense_{{$item->slug}}">@if($expense > 0){{number_format( $expense, 2, ',', ' ')}} € @else - @endif</span>
                                <input type="hidden" value="{{$expense}}" id="input_expense_{{$item->slug}}"></inpput>
                            </td>
                            <td {!!$color!!} class="row_color_{{$item->slug}}"><span id="display_expense_percentage_{{$item->slug}}">@if($expense > 0){{number_format( $diff_percent, 2, ',', ' ')}} % @else - @endif</span></td>
                            <td {!!$color!!} class="row_color_{{$item->slug}}"><span id="display_expense_value_{{$item->slug}}">@if($expense > 0){{number_format( $diff_value, 2, ',', ' ')}} € @else - @endif</span></td>
                        </tr>
                            
                    @else

                        <tr class="{{$slug}}_Content">
                            <td {!!$color!!} class="row_color_{{$item->slug}} text-right">{{ $item->name }}</td>
                            <td {!!$color!!} class="row_color_{{$item->slug}}" style="width: 105px !important" onclick="updateForecastValue('{{$item->slug}}')">
                                {{ number_format($item->forecast, 2, ',', '.') }}
                                <input type="hidden" value="{{$item->forecast}}" id="forecast_{{$slug}}_{{$item->slug}}"></inpput>
                            </td>
                        
                            <td {!!$color!!} class="row_color_{{$item->slug}}" onclick="$('.{{$item->slug}}_Content_details').toggle();">
                                <span style="color: black; font-weight: bolder;cursor: pointer;" id="display_expense_{{$item->slug}}">@if($expense > 0){{number_format( $expense, 2, ',', ' ')}} € @else - @endif</span>
                                <input type="hidden" value="{{$expense}}" id="input_expense_{{$item->slug}}"></inpput>
                            </td>
                            <td {!!$color!!} class="row_color_{{$item->slug}}"><span id="display_expense_percentage_{{$item->slug}}">@if($expense > 0){{number_format( $diff_percent, 2, ',', ' ')}} % @else - @endif</span></td>
                            <td {!!$color!!} class="row_color_{{$item->slug}}"><span id="display_expense_value_{{$item->slug}}">@if($expense > 0){{number_format( $diff_value, 2, ',', ' ')}} € @else - @endif</span></td>
                        </tr>
                        <tr class="{{$item->slug}}_Content_details" style="display: none;">
                            <td colspan="5">
                                <table class="table table-striped" style="width: 100%;text-align: center;margin-bottom: 0;display: table;">
                                    <tr>
                                        <td style="display: none;"></td>
                                        <td style="text-align: center;width: 10% !important;max-width: 10% !important"></td>
                                        <td style="text-align: left;width: 65%;">Expense</td>
                                        <td style="text-align: left;width: 25%;">Value</td>
                                    </tr>
                                    @if( isset($details[$item->slug]) )
                                        @foreach($details[$item->slug] AS $detail)
                                            <tr>
                                                <td style="display: none;"></td>
                                                <td style="text-align: center;cursor:pointer;padding-right: 10px;width: 10% !important;min-width: 10px !important;" onclick="deleteDetail({{$detail->id}})"><i class="fa-solid fa-trash" style="color: red;"></i></td>
                                                <td style="text-align: left;" onclick="updateDetail({{$detail->id}},'{{$detail->detail}}', '{{number_format( $detail->amount, 2, ',', ' ')}}', '{{$item->slug}}', '{{$slug}}')">{{$detail->detail}}</td>
                                                <td style="text-align: left;" onclick="updateDetail({{$detail->id}},'{{$detail->detail}}', '{{number_format( $detail->amount, 2, ',', ' ')}}', '{{$item->slug}}', '{{$slug}}')">
                                                    <span style="color: black; font-weight: bolder;cursor: pointer;" id="display_detail_{{$item->slug}}">{{number_format( $detail->amount, 2, ',', ' ')}} €</span>
                                                    <input type="hidden" value="{{$detail->amount}}" id="input_detail_{{$detail->amount}}"></inpput>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                        <tr>
                                            <td colspan="3">
                                                <button class="btn btn-success" type="button" onclick="updateDetailValue('{{$item->slug}}', 'addDetail', '{{$slug}}')" style="margin: 10px;">
                                                    ADD EXPENSE FOR <span style="text-transform: uppercase; font-weight: bolder">{{ $item->name }}</span>
                                                </button>
                                            </td>
                                        </tr>
                                </table>
                            </td>
                        </tr>
                            
                    @endif
                @endforeach
                <tr class="{{$slug}}_totals_Content {{$slug}}_Content">
                    <td {!!$color_total!!} class="text-right">TOTALS</td>
                    <td {!!$color_total!!}><span id="display_expense_forecast_{{$slug}}">{{number_format( $total_forecast, 2, ',', ' ')}} €</span></td>
                    <td {!!$color_total!!}><span id="display_expense_forecast_{{$slug}}">{{number_format( $total_expenses, 2, ',', ' ')}} €</span></td>
                    <td {!!$color_total!!}><span id="display_expense_percentage_{{$slug}}">{{number_format( $total_diff_percent, 2, ',', ' ')}} %</span></td>
                    <td {!!$color_total!!}><span id="display_expense_value_{{$slug}}">{{number_format( $total_diff, 2, ',', ' ')}} €</span></td>
                </tr>
            
                <tr class="{{$slug}}_totals_Content {{$slug}}_Content" style="display: none;">
                    <td colspan="5" style="padding-top: 10px;background-color: #FFF;color: #FFF;"></td>
                </tr>
            
                <tr class="{{$slug}}_totals_Content {{$slug}}_Content" style="display: none;">
                    <td colspan="5" style="padding-top: 10px;background-color: darkgrey;color: #FFF;" onclick="$('.{{$slug}}_totals_Content_stats').toggle();">YEARLY EXPENSES STATUS</td>
                </tr>
                <tr class="{{$slug}}_totals_Content_stats" style="display: none !important;text-align: center;">
                    <td colspan="2" style="width: 50px;text-align: right;">NAME</td>
                    <td>FORECAST</td>
                    <td>EXPENSES</td>
                    <td>%</td>
                </tr>
                @foreach($status_year[$slug] AS $key => $status)
                    <tr class="{{$slug}}_totals_Content_stats" style="display: none !important;font-size: 18px;text-align: center;">
                        <td colspan="2" style="width: 50px;text-align: right;"><span>{{$status['name']}}</span> {!!$status['icon']!!}</td>
                        <td style="text-align: center;color: {{$status['color']}}">{{number_format($status['forecast'], 2, ',', ' ')}} €</td>
                        <td style="text-align: center;color: {{$status['color']}}">{{number_format($status['expense'], 2, ',', ' ')}} €</td>
                        <td style="text-align: center;color: {{$status['color']}}"> {{number_format($status['percentage'], 2, ',', ' ')}} %</td>
                    </tr>
                @endforeach
            @else
                Não encontrado
            @endif
        </table>
    </div>
</div>