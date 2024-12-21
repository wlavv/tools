<div class="col-lg-12">
    <div class="navbar navbar-light customPanel">
        <div class="row">
            <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                <div class="card-counter dark" onclick="window.location.href = '{{route('oriflame.list', 1)}}';">
                    <div style="width: 80px;float: left;"> <i class="fa fa-boxes-packing"></i> </div>
                    <div style="width: calc( 80% - 100px); float: left;padding: 10px 0;">
                        <span class="count-numbers">{{$counters['all']}}</span>
                        <br><span class="count-name">All</span>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                <div class="card-counter info" onclick="window.location.href = '{{route('oriflame.list', 1)}}';">
                    <div style="width: 80px;float: left;"> <i class="fa-solid fa-box-open"></i> </div>
                    <div style="width: calc( 80% - 100px); float: left;padding: 10px 0;">
                        <span class="count-numbers">{{$counters['open']}}</span>
                        <br><span class="count-name">Open</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                <div class="card-counter primary" onclick="window.location.href = '{{route('oriflame.list', 2)}}';">
                    <div style="width: 80px;float: left;"> <i class="fa fa-code-fork"></i> </div>
                    <div style="width: calc( 100% - 80px); float: left;padding: 10px 0;">
                        <div class="count-numbers">{{$counters['cancelled']}}</div>
                        <br><div class="count-name">Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                <div class="card-counter success" onclick="window.location.href = '{{route('oriflame.list', 3)}}';">
                    <div style="width: 80px;float: left;"> <i class="fa fa-truck-fast"></i> </div>
                    <div style="width: calc( 80% - 100px); float: left;padding: 10px 0;">
                        <span class="count-numbers">{{$counters['shipped']}}</span>
                        <br><span class="count-name">Shipped</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-sm-6 col-6">
                <div class="card-counter danger" onclick="window.location.href = '{{route('oriflame.list', 4)}}';">
                    <div style="width: 80px;float: left;"> <i class="fa fa-xmark"></i> </div>
                    <div style="width: calc( 100% - 80px); float: left;padding: 10px 0;">
                        <span class="count-numbers">{{$counters['cancelled']}}</span>
                        <br><span class="count-name">Cancelled</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <button class="btn btn-success float-end" type="button" style="padding: 12px" onclick="newOrder()">
                    <i class="fa-solid fa-cart-plus" style="font-size: 30px;"></i>
                    <br>
                    <div style="margin-top: 10px;">NEW ORDER</div>
                </button>                        
            </div>
        </div>
    </div>           
</div>