<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\webToolsManager\wt_orders_details;
use App\Models\webToolsManager\wt_customer;

use Illuminate\Support\Facades\Config;

class wt_orders extends Model
{   
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = true;
    protected $primaryKey = 'id_order';


    public function __construct()
    {
        $this->table = env('DB2_prefix')."orders";
    }

    public function order_detail(){
        return $this->hasMany(wt_orders_details::class, "id_order", 'id_order');
    }

    public static function getOrders($current_state = 0){

        if($current_state == 0) return wt_orders::get();

        return wt_orders::where('current_state', $current_state)->get();
    }

    public static function getCounters(){

        return [
            'all' => wt_orders::count(),
            'open' => wt_orders::where('current_state', 1)->count(),
            'closed' => wt_orders::where('current_state', 2)->count(),
            'shipped' => wt_orders::where('current_state', 3)->count(),
            'cancelled' => wt_orders::where('current_state', 4)->count(),
        ];
    }
    

    public static function addProduct($data){

        $openOrder = wt_orders::where('id_customer', $data['id_customer'])->first();

        $id_order = 0;
        if(isset($openOrder->id_customer) ){
            $id_order = $openOrder->id_order;

            $openOrder->total += $data['price'];
            $openOrder->update();
        }else{
            $id_order = wt_orders::createOrder($data['id_customer'], $data['price']);
        }

        wt_orders_details::addProduct($id_order, $data);

    }

    public static function createOrder($id_customer, $price){

        $newOrder = new wt_orders();

        $newOrder->id_customer = $id_customer;
        $newOrder->current_state = 1;
        $newOrder->total = $price;
        $newOrder->save();

        return $newOrder->id_order;
    }
}
