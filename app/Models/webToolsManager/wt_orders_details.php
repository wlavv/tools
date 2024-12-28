<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

use App\Models\webToolsManager\wt_product;

class wt_orders_details extends Model
{   
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;
    protected $primaryKey = 'id_order_details';

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_details";
    }

    public function product()
    {
        return $this->hasOne(wt_product::class, 'id_product', 'id_product');
    }

    public static function exists($id_order, $id_product){
        return wt_orders_details::where('id_order', $id_order)->where('id_product', $id_product)->count();
    }

    public static function addProduct($id_order, $data){

        $found = self::exists($id_order, $data['id_product']);
        if($found > 0){
            $row = wt_orders_details::where('id_order', $id_order)->where('id_product', $data['id_product'])->first();

            wt_orders_details::where('id_order', $id_order)->where('id_product', $data['id_product'])->update(
                [
                    'quantity' => $row->quantity + $data['quantity'],
                    'total'    => ($row->quantity + $data['quantity']) * $data['unitary_price']
                ]
            );

            wt_orders::updateOrderTotal($id_order);
        }else{

            $row = new wt_orders_details();

            $row->id_order = $id_order;
            $row->id_product = $data['id_product'];
            $row->id_product_attribute = '';
            $row->reference = $data['reference'];
            $row->unitary_price = $data['price'];
            $row->quantity = $data['quantity'];
            $row->price = $data['quantity'] * $data['price'];
            $row->save();

        }

        return 1;
    }

    public static function updateQuantity($id_order, $id_product, $quantity){
        $row = wt_orders_details::where('id_order', $id_order)->where('id_product', $id_product)->first();
        
        wt_orders_details::where('id_order', $id_order)->where('id_product', $id_product)->update(['quantity' => $quantity, 'price' => ( $quantity * $row->unitary_price) ]);

        wt_orders::updateOrderTotal($id_order);

    }

    public static function removeProduct($id_order, $id_product){
        wt_orders_details::where('id_order', $id_order)->where('id_product', $id_product)->delete();
        wt_orders::updateOrderTotal($id_order);
    }
    
    public static function getProductOfOrder($id_order){
        return wt_orders_details::where('id_order', $id_order)->get();
    }
}
