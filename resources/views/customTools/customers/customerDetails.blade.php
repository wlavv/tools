<div id="customerOrderInfo" style="margin-top: 20px;">
    <div class="row">
        <input type="hidden" id="selected_customer" value="{{$customer[0]->id_customer}}">
        <div class="col-6" style="text-align: right; font-weight: bolder;">Name: </div>
        <div class="col-6">{{$customer[0]->firstname}} {{$customer[0]->lastname}}</div>
        <div class="col-12"></div>
        <div class="col-6" style="text-align: right; font-weight: bolder;">Email: </div>
        <div class="col-6">{{$customer[0]->email}}</div>
        <div class="col-12"></div>
        <div class="col-6" style="text-align: right; font-weight: bolder;">Phone: </div>
        <div class="col-6">{{$customer[0]->phone}}</div>
    </div>
</div>