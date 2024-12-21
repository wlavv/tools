<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

class orders_details extends Model
{   
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_detail";
    }

    public function product(){
        return $this->hasOne(product::class, "id_product", 'product_id');
    }

    public function product_attribute(){
        return $this->hasMany(product_attribute::class, "id_product_attribute", 'product_attribute_id');
    }


    public static function getSoldOf($product_reference, $attr_reference = ''){

        $reference = (strlen($attr_reference) > 0) ? $attr_reference : $product_reference;

        return DB::table(env('DB2_DB_prefix') . 'order_detail')
        ->join(       env('DB2_DB_prefix') . 'orders',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
        ->where(      env('DB2_DB_prefix') . 'orders.date_add', '>', date('Y-m-d', strtotime('-1 year')) )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $reference )
        ->whereIn(      env('DB2_DB_prefix') . 'orders.current_state', [2, 3, 4, 5, 15, 16, 28] )
        ->sum('product_quantity');

    }

    public static function getSoldByIDOf($id_product, $id_product_attribute = 0){

        return DB::table(env('DB2_DB_prefix') . 'order_detail')
        ->join(       env('DB2_DB_prefix') . 'orders',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
        ->where(      env('DB2_DB_prefix') . 'orders.date_add', '>', date('Y-m-d', strtotime('-1 year')) )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_id', $id_product )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_attribute_id', $id_product_attribute )
        ->whereIn(      env('DB2_DB_prefix') . 'orders.current_state', [2, 3, 4, 5, 15, 16, 28] )
        ->sum('product_quantity');

    }

    public static function getSoldByRefOf($reference){

        return DB::table(env('DB2_DB_prefix') . 'order_detail')
        ->join(       env('DB2_DB_prefix') . 'orders',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
        ->where(      env('DB2_DB_prefix') . 'orders.date_add', '>', date('Y-m-d', strtotime('-1 year')) )
        ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $reference )
        ->whereIn(      env('DB2_DB_prefix') . 'orders.current_state', [2, 3, 4, 5, 15, 16, 28] )
        ->sum('product_quantity');

    }

    public static function getProductsOfOrder($id_order){
        
        $list = '';
        $products = self::select('product_reference')->where('id_order', $id_order)->get();
        
        foreach($products AS $product) $list .= $product->product_reference . ', ';
        
        return substr($list, 0, -2);
    }

    public static function orders_without_shipping_product($id_store = 3){

        return DB::table(env('DB2_DB_prefix') . 'order_detail')
        ->join(       env('DB2_DB_prefix') . 'orders',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
        ->where(      env('DB2_DB_prefix') . 'orders.id_shop', $id_store )
        ->where(      env('DB2_DB_prefix') . 'orders.current_state', 4 )
        ->whereNOT(      env('DB2_DB_prefix') . 'order_detail.product_reference', 'LIKE', 'SHIPPING-%' )
        ->get();

    }

    public static function dashboard_orders_without_shipping_product($type, $id_store){

        $data = array();

        $bd_data = self::orders_without_shipping_product($id_store);
        
        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference];

        return [
            'name'              => trans('dashboard.Without shipping'),
            'col'               => 4,
            'item_id'           => $type . '_without_shipping',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['id_order', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];  ;        
    } 
    
    
    
}
