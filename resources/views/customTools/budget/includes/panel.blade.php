
<div class="col-lg-4">    
    <div class="navbar navbar-light customPanel">
        <table class="table table-striped" style="width: 100%;text-align: center;margin-bottom: 0;">
            <tr>
            <td colspan="4" style="text-align: center;cursor: pointer; width:90%; vertical-align: middle;font-size: 25px;" onclick="$('.{{$slug}}Content').toggle()"> {{$title}} </td>
            <td colspan="1" style="text-align: center;cursor: pointer" onclick="alert('SHOW GRAPH')"> 
                <button class="btn btn-primary" style="color: #FFF;width: 40px;height: 40px;float: right;margin-right: 10px;">
                    <i class="fa-solid fa-chart-simple"></i> 
                </button>
            </td>
            </tr>
            <tr class="{{$slug}}Content">
                <td></td>
                <td>Previsto</td>
                <td>Gasto</td>
                <td>%</td>
                <td>€</td>
            </tr>

            @if(isset($row) && isset($row->sons))
                @foreach($row->sons as $key => $item)
                    <tr class="{{$slug}}Content">
                        <td style="width: 200px !important;"class="text-right">{{ $item->name }}</td>
                        <td style="width: 80px !important;">{{ number_format($item->forecast, 2, ',', '.') }} €</td>
                        <td style="width: 80px !important;">0.00 €</td>
                        <td style="width: 80px !important;">0.00%</td>
                        <td style="width: 80px !important;">xx.xx €</td>
                    </tr>
                @endforeach
            @else
                Não encontrado
            @endif
        </table>
    </div>
</div>