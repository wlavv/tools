<div id="customersList">
    <table class="table table-striped table-hover" style="width: 95%; margin: 0 auto;margin-top: 20px;">
        <tr>
            <td>Name</td>
            <td>E-mail</td>
            <td>Phone</td>
        </tr>
        @foreach($customer AS $row)
            <tr onclick="setCustomer('{{$row->email}}')" style="cursor: pointer;">
                <td>{{$row->firstname}} {{$row->lastname}}</td>
                <td>{{$row->email}}</td>
                <td>{{$row->phone}}</td>
            </tr>
        @endforeach
    </table>
</div>