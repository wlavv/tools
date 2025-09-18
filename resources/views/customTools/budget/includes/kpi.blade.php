@if( !$isMobile ) <div class="col-lg-12"> <div class="spacer-10"></div> </div> @endif
<div class="col-lg-12">    
    <div class="navbar navbar-light customPanel" @if( $isMobile ) style="margin-top: 0;" @endif>

        @php
            $months = [
                1 => 'JANUARY',
                2 => 'FEBRUARY',
                3 => 'MARCH',
                4 => 'APRIL',
                5 => 'MAY',
                6 => 'JUNE',
                7 => 'JULY',
                8 => 'AUGUST',
                9 => 'SEPTEMBER',
                10 => 'OCTOBER',
                11 => 'NOVEMBER',
                12 => 'DECEMBER',
            ];
        @endphp
        
        <div style="width: 100%; height: 42px;">
            <div style="float: left; margin: 9px 5px 5px 0;">SELECTED MONTH: </div>
            <select id="monthSelector" class="form-select" style="width: 250px; font-size: 18px;float: left;" onchange="window.location.href=this.value">
                @foreach ($months as $number => $name)
                    <option @if($number == $month) selected="selected" @endif value="{{ route('budget.index', ['year' => date('Y'), 'month' => $number]) }}">
                        {{ $name }}
                    </option>
                @endforeach
            </select>            
        </div>
    </div>
</div>

@if( !$isMobile ) <div class="col-lg-12"> <div class="spacer-10"></div> </div> @endif

<div class="col-lg-4">
    <div class="card text-center bg-success text-white" @if( $isMobile ) style="border-radius: 0" @endif>
        @if( $isMobile )
            <div class="card-header" style="font-weight: bolder;font-size: 15px;cursor: pointer;border-bottom: none;" onclick="$('#incomeDetail').toggle()"> 
                <span>RENDIMENTOS: </span>
                <span style="font-size: 15px;" id="display_total_kpi_income">{{number_format($total_income, 2, ',', ' ')}} €</span>
            </div>        
        @else
            <div class="card-header" style="font-weight: bolder;font-size: 25px;cursor: pointer;border-bottom: none;" onclick="$('#incomeDetail').toggle()"> 
                <div>RENDIMENTOS</div>
                <div style="font-size: 50px;" id="display_total_kpi_income">{{number_format($total_income, 2, ',', ' ')}} €</div>
            </div>
        @endif
        <div class="card-body" style="display: none; margin: 0 10px 10px 10px;" id="incomeDetail">
            <table style="width: 100%; margin: 0;padding: 0;" class="table table-striped shortSpacing">
                <tr>
                    <td class="text-right" style="padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;width: 40%;">DATA:</td>
                    <td style="padding-left: 5px;">
                        <table style="width: 100%; border: 1px solid #EBEDEF;background-color: #EBEDEF;margin: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;">ANO:</td>
                                <td style="width: 50%;padding-left: 5px;background-color: #EBEDEF;cursor: pointer;"> 
                                    <span id="display_year">{{$year}}</span> 
                                    <input type="hidden" value="{{$year}}" id="input_year"> 
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;">MÊS:</td>
                                <td style="width: 50%;padding-left: 5px;background-color: #EBEDEF;cursor: pointer;">
                                    <span id="display_month">{{$month}}</span> 
                                    <input type="hidden" value="{{$month}}" id="input_month"> 
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">BRUNO:</td>
                    <td style="padding-left: 5px;">
                        <table style="width: 100%; border: 1px solid #EBEDEF;border: 1px solid #FFF;margin-bottom: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;">Vencimento:</td>
                                <td onclick="updateValue('bruno_salary', 'income', '')" style="width: 50%;padding-left: 5px;cursor: pointer;"> 
                                    <span id="display_bruno_salary">{{number_format($bruno_salary, 2, ',', ' ')}} €</span>
                                    <input type="hidden" value="{{$bruno_salary}}" id="input_bruno_salary"></inpput>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;">ASM:</td>
                                <td onclick="updateValue('bruno_asm', 'income', '')" style="width: 50%;padding-left: 5px;cursor: pointer;">
                                    <span id="display_bruno_asm">{{number_format($bruno_asm, 2, ',', ' ')}} €</span>
                                    <input type="hidden" value="{{$bruno_asm}}" id="input_bruno_asm">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;">CV:</td>
                                <td onclick="updateValue('bruno_cv', 'income', '')" style="width: 50%;padding-left: 5px;cursor: pointer;">
                                    <span id="display_bruno_cv">{{number_format($bruno_cv, 2, ',', ' ')}} €</span>
                                    <input type="hidden" value="{{$bruno_cv}}" id="input_bruno_cv">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;font-weight: bolder;">TOTAL:</td>
                                <td style="width: 50%;padding-left: 5px;font-weight: bolder;color: darkgreen;"> <span id="display_bruno_income">{{number_format($bruno_income, 2, ',', ' ')}} €</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">MÁRCIA:</td>
                    <td style="padding-left: 5px;">
                        <table style="width: 100%; margin-bottom: 0;padding: 3px;border: 1px solid #EBEDEF" class="table shortSpacing">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;">Vencimento:</td>
                                <td onclick="updateValue('marcia_salary', 'income', '')" style="width: 50%;padding-left: 5px;cursor: pointer;background-color: #EBEDEF;">
                                    <span id="display_marcia_salary">{{number_format($marcia_salary, 2, ',', ' ')}} €</span>
                                    <input type="hidden" value="{{$marcia_salary}}" id="input_marcia_salary"></inpput>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;">Oriflame:</td>
                                <td onclick="updateValue('marcia_oriflame', 'income', '')" style="width: 50%;padding-left: 5px;cursor: pointer;background-color: #EBEDEF;">
                                    <span id="display_marcia_oriflame">{{number_format($marcia_oriflame, 2, ',', ' ')}} €</span>
                                    <input type="hidden" value="{{$marcia_oriflame}}" id="input_marcia_oriflame"></inpput>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;font-weight: bolder;background-color: #EBEDEF;">TOTAL:</td>
                                <td style="width: 50%;padding-left: 5px;font-weight: bolder;color: darkgreen;background-color: #EBEDEF;"> <span id="display_marcia_income">{{number_format($marcia_income, 2, ',', ' ')}} €</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 35%;padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">MENINAS:</td>
                    <td style="width: 65%;padding-left: 5px;">
                        <table style="width: 100%; border: 1px solid #FFF;margin-bottom: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;"></td>
                                <td onclick="updateValue('meninas_income', 'income', '')" style="width: 50%;padding-left: 5px;cursor: pointer;">
                                    <span id="display_meninas_income">{{number_format($meninas_income, 2, ',', ' ')}} €</span>
                                    <input type="hidden" value="{{$meninas_income}}" id="input_meninas_income"></inpput>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 35%;padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;">EXTRA:</td>
                    <td style="width: 65%;padding-left: 5px;">
                        <table style="width: 100%;border: 1px solid #EBEDEF;margin-bottom: 0;padding: 3px;" class="table">
                            <tr>
                                <td class="text-right" style="width: 50%;padding-right: 5px;background-color: #EBEDEF;"></td>
                                <td onclick="updateValue('extra_income', 'income', '')" style="width: 50%;padding-left: 5px;cursor: pointer;background-color: #EBEDEF;">
                                    <span id="display_extra_income">{{number_format($extra_income, 2, ',', ' ')}} €</span>
                                    <input type="hidden" value="{{$extra_income}}" id="input_extra_income"></inpput>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 35%;padding-right: 5px;vertical-align: middle;border-right: 1px solid #bbb;font-weight: bolder;">TOTAL:</td>
                    <td style="width: 50%;padding-left: 5px;">
                        <table style="width: 100%;margin-bottom: 0;padding: 3px;" class="table shortSpacing">
                            <tr>
                                <td colspan="2" style="text-align: center;padding-left: 5px;color: darkgreen;font-weight: bolder;font-size: 30px;border-bottom: 0;"> <span id="display_total_income">{{number_format($total_income, 2, ',', ' ')}} €</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>    
</div>
<div class="col-lg-4">
    <div class="card text-center text-white bg-danger" @if( $isMobile ) style="border-radius: 0" @endif>
        @if( $isMobile )
            <div class="card-header" style="font-weight: bolder;font-size: 15px;cursor: pointer;border-bottom: none;" onclick="$('#expenseDetail').toggle()"> 
                <span>DESPESAS: </span>
                <span style="font-size: 15px;" id="month_total_expense">{{number_format($expenses, 2, ',', ' ')}} €</span>
            </div>        
        @else
            <div class="card-header" style="font-weight: bolder;font-size: 25px;cursor: pointer;border-bottom: none;" onclick="$('#expenseDetail').toggle()">
                <div>DESPESAS</div>
                <div style="font-weight: bolder;font-size: 50px;" id="month_total_expense">{{number_format($expenses, 2, ',', ' ')}} €</div>
            </div>
        @endif
        <div class="card-body" style="display: none; margin: 0 10px 10px 10px;" id="expenseDetail">
            <table style="width: 100%; margin: 0;padding: 0;" class="table table-striped shortSpacing">
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">POTES:</td>
                    <td style="width: 50%;padding-left: 5px;"> <span id="display_potes">{{number_format($potes, 2, ',', ' ')}} €</span> </td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">FORECAST:</td>
                    <td style="width: 50%;padding-left: 5px;"> <span id="display_forecast">{{number_format($forecast, 2, ',', ' ')}} €</span></td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">EXPENSES:</td>
                    <td style="width: 50%;padding-left: 5px;"> <span id="display_expenses">{{number_format($expenses, 2, ',', ' ')}} €</span></td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">DIFFERENCE:</td>
                    <td style="width: 50%;padding-left: 5px;"> <span id="display_difference">{{number_format( $differences , 2, ',', ' ')}} €</span></td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">PERCENTAGE:</td>
                    <td style="width: 50%;padding-left: 5px;"> <span id="display_percentage">{{number_format( $budget_percentage , 2, ',', ' ')}} %</span></td>
                </tr>
                <tr>
                    <td class="text-right" style="width: 50%;padding-right: 5px;">MAX:</td>
                    <td style="width: 50%;padding-left: 5px;"> <span id="display_budget_max">{{number_format( $budget_max , 2, ',', ' ')}} €</span></td>
                </tr>
            </table>
        </div>
    </div>
</div>        
<div class="col-lg-4">
    <div class="card text-center text-white bg-info"  @if( $isMobile ) style="border-radius: 0"@endif>
        @if( $isMobile )
            <div class="card-header" style="font-weight: bolder;font-size: 15px;cursor: pointer;border-bottom: none;"> 
                <span>POUPANÇA: </span>
                <span style="font-size: 15px;" id="month_total_expense">{{number_format($savings, 2, ',', ' ')}} €</span>
            </div>        
        @else
            <div class="card-header" style="font-weight: bolder;font-size: 25px;border-bottom: none;"> 
                <div>POUPANÇA</div>
                <div style="font-weight: bolder;font-size: 50px;">{{number_format($savings, 2, ',', ' ')}} €</div>
            </div>
        @endif
    </div>
</div>