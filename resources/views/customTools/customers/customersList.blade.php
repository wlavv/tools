<div id="customersList">
    <table class="table table-hover" style="max-width: 450px; margin: 0 auto;margin-top: 20px;text-align: center;">
        @foreach($customer AS $row)
            <tr onclick="setCustomer('{{$row->email}}')" style="cursor: pointer;vertical-align: middle;">
                <td>
                    <div>{{$row->firstname}} {{$row->lastname}}</div>
                </td>
                <td>
                    <div style="text-align: left;padding: 5px;"><i style="color: dodgerblue;" class="fa-solid fa-envelope"></i> {{$row->email}}</div>
                    <div style="text-align: left;padding: 5px;"><i style="color: dodgerblue;" class="fa-solid fa-phone"></i> {{$row->phone}}</div>
                </td>
            </tr>
        @endforeach
    </table>
</div>