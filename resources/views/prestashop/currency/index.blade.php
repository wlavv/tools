@extends('layouts.app')
@section('content')

<div class="row">
    <div class="col-lg-6">
        <div class="navbar navbar-light customPanel">
            <div style="background-color: #ddd; height: 50px; padding: 5px;">
                <div style="margin: 5px;">
                    <span style="border-radius: 50px; background-color: #666; color: #FFF;height: 30px; float: left;padding: 3px 5px">{{count($currencies)}}</span> <div style="margin: 5px 10px;float: left;">{{ __("messages.CURRENCIES")}}</div>
                </div>
            </div>
            <table class="table table-striped text-center table-hover" style="width: 100%;">
                <thead>
                    <tr>
                        <td>{{ __("messages.ID")}}</td>
                        <td>{{ __("messages.Active")}}</td>
                        <td>{{ __("messages.Name")}}</td>
                        <td>{{ __("messages.Conversion rate")}}</td>
                    </tr>
                </thead>
                @foreach($currencies AS $currency)
                    <tr onclick="openForm({{$currency->id_currency}})" style="cursor: pointer;">
                        <td>{{$currency->id_currency}}</td>
                        <td>                                        
                            @if($currency->active == 1) <i class="fa-solid fa-check" style="color: darkgreen; font-size: 18px;"></i> 
                            @else <i class="fa-solid fa-xmark" style="color: red; font-size: 18px;"></i> 
                            @endif    
                        </td>
                        <td>{{$currency->name}}</td>
                        <td>{{$currency->conversion_rate}}</td>
                    </tr>
                @endforeach
            </table>
        </div>        
    </div>
    <div class="col-lg-6">
        <div class="navbar navbar-light customPanel">
            <div id="noCurrencySelected">
                <div class="alert alert-info" role="alert">{{ __("messages.Please select a row on the left to edit!")}}</div>
            </div>
            @foreach($currencies AS $currency)
                <div class="closeCurrencies" id="currency_{{$currency->id_currency}}" style="display: none;">
                    <div class="form-row">
                        <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                            <div class="input-group-prepend" style="width: 170px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Active")}}</div> </div>
                            <div style="padding: 5px;" class="form-control">
                                <div style="float: left; width: 80px;text-align: center;">
                                    <label> {{ __("messages.YES")}} <input class="active_{{$currency->id_currency}}" name="active_{{$currency->id_currency}}" value="1" type="radio" required @if( $currency->active == 1 ) checked="checked" @endif></label>
                                </div>
                                <div style="float: left; width: 80px;text-align: center;">
                                    <label> {{ __("messages.NO")}}  <input class="active_{{$currency->id_currency}}" name="active_{{$currency->id_currency}}" value="0" type="radio" required @if( $currency->active == 0 ) checked="checked" @endif></label>                                
                                </div>
                            </div>
                        </div>
                        <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                            <div class="input-group-prepend" style="width: 170px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Name")}}</div> </div>
                            <input id="name_{{$currency->id_currency}}" name="name_{{$currency->id_currency}}" type="text" class="form-control" value="{{$currency->name}}" required>
                        </div>
                        <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                            <div class="input-group-prepend" style="width: 170px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Conversion rate")}}</div> </div>
                            <input id="conversion_rate_{{$currency->id_currency}}" name="conversion_rate_{{$currency->id_currency}}" type="text" class="form-control" value="{{$currency->conversion_rate}}" required>
                        </div>
                        <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                            <div class="input-group-prepend" style="width: 170px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.Precision")}}</div> </div>
                            <input id="precision_{{$currency->id_currency}}" name="precision_{{$currency->id_currency}}" type="text" class="form-control" value="{{$currency->precision}}" required>
                        </div>
                        <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                            <div class="input-group-prepend" style="width: 170px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.ISO CODE")}}</div> </div>
                            <input id="iso_code_{{$currency->id_currency}}" name="iso_code_{{$currency->id_currency}}" type="text" class="form-control" value="{{$currency->iso_code}}" required>
                        </div>
                        <div class="input-group" style="border-radius: 0 5px 5px 0; margin-bottom: 10px;">
                            <div class="input-group-prepend" style="width: 170px; float: left;"> <div class="input-group-text" style="border-radius: 5px 0 0 5px;text-align: right;">{{ __("messages.NUMERIC ISO CODE")}}</div> </div>
                            <input id="numeric_iso_code_{{$currency->id_currency}}" name="numeric_iso_code_{{$currency->id_currency}}" type="text" class="form-control" value="{{$currency->numeric_iso_code}}" required>
                        </div>
                        <div style="text-align: right;">
                            <button class="btn btn-success" onclick="updateCurrencyInfo({{$currency->id_currency}})"> <i class="fa-solid fa-save" style="font-size: 18px;"></i>{{ __("messages.UPDATE")}}</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>        
    </div>
</div>

<style>
    .input-group-text{ display: block; }
</style>
<script>
    
    function openForm(id_currency){
        
        $('#noCurrencySelected').css('display', 'none');
        $('.closeCurrencies').css('display', 'none');
        $('#currency_' + id_currency).css('display', 'block');
        
    }
    
    function updateCurrencyInfo(id_currency){                
        
        active = document.querySelector('input[name="active_' + id_currency + '"]:checked').value;

        $.ajax({
            type: 'POST',
            url: "{{route('currency.store')}}",
            data: {
                _token: "{{ csrf_token() }}",
                id_currency: id_currency,
                name: $('#name_' + id_currency).val(),
                active: active,
                conversion_rate: $('#conversion_rate_' + id_currency).val(),
                precision: $('#precision_' + id_currency).val(),
                iso_code: $('#iso_code_' + id_currency).val(),
                numeric_iso_code: $('#numeric_iso_code_' + id_currency).val(),
            },
            success: function(response) {
                Swal.fire("Changes saved successfully!", "", "success");
            }       
        });
        
    }
    
</script>
@endsection