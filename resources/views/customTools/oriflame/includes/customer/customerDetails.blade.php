<div id="customerOrderInfo">
    <div style="margin-left: 30px; border-left: 2px solid #bbb;">
        <div class="row" style="margin-left: 30px; ">
            <div class="col-lg-4">ID customer: </div>
            <div class="col-lg-8">{{$customer[0]->id_customer}}</div>
            <div class="col-lg-12"></div>
            <div class="col-lg-4">Name: </div>
            <div class="col-lg-8">{{$customer[0]->firstname}} {{$customer[0]->lastname}}</div>
            <div class="col-lg-12"></div>
            <div class="col-lg-4">Email: </div>
            <div class="col-lg-8">{{$customer[0]->email}}</div>
            <div class="col-lg-12"></div>
            <div class="col-lg-4">Phone: </div>
            <div class="col-lg-8">{{$customer[0]->phone}}</div>
        </div>
    </div>
</div>