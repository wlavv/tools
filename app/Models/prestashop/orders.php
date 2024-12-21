<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\customer;

use Illuminate\Support\Facades\Config;

class orders extends Model
{   
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = true;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."orders";
    }

    public function order_detail(){
        return $this->hasMany(orders_details::class, "id_order", 'id_order');
    }

    public static function getOrders(){
        return orders::get();
    }

    public static function addProduct($data){

        $openOrder = orders::where('id_customer', $data['id_customer'])->first();

        $id_order = 0;
        if(isset($openOrder->id_customer) ){
            $id_order = $openOrder->id_order;
        }else{
            $id_order = orders::createOrder($data['id_customer'], $data['price']);
        }

        orders_details::addProduct($id_order, $data);

    }

    public static function createOrder($id_customer, $price){

        $newOrder = new orders();

        $newOrder->id_customer = $id_customer;
        $newOrder->current_state = 1;
        $newOrder->total = $price;
        $newOrder->save();

        return $newOrder->id_order;
    }
}
