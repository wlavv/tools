<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\prestashop\orders_details;
use App\Models\prestashop\orders_history;
use App\Models\prestashop\customer;
use App\Models\prestashop\addresses;

use Illuminate\Support\Facades\Config;

class orders extends Model
{   
    protected $connection = 'mysql2';
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."orders";
    }

    public function order_detail(){
        return $this->hasMany(orders_details::class, "id_order", 'id_order');
    }

    public function customer(){
        return $this->hasOne(customer::class, "id_customer", 'id_customer');
    }

    public function delivery(){
        return $this->hasOne(address::class, "id_address", 'id_address_delivery');
    }

    public function invoice(){
        return $this->hasOne(address::class, "id_address", 'id_address_invoice');
    }

    public static function dashboard_partial_orders($type){

        $data = array();
        
        $bd_data = Orders::select('id_order', 'reference')->where('current_state', 28)->get();

        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference];
        
        return [
            'name'              => trans('dashboard.PARTIAL ORDERS'),
            'col'               => 4,
            'item_id'           => $type . '_partial_orders',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['id_order', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    } 

    public static function getParcials($product_reference){

        $tempData = DB::table(env('DB2_DB_prefix') . 'orders')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'orders.current_state', 28 )
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $product_reference )
            ->get();

        return json_decode($tempData, true);

    }

    public static function getPreparations($product_reference){

        $tempData = DB::table(env('DB2_DB_prefix') . 'orders')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'orders.current_state', 3 )
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $product_reference )
            ->get();

        return json_decode($tempData, true);

    }

    public static function getBackorders($product_reference){

        $tempData = DB::table(env('DB2_DB_prefix') . 'orders')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'orders.current_state', 15 )
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', $product_reference )
            ->get();

        return json_decode($tempData, true);

    }

    public static function getAllOrdersOf($id_current_state){

        $data = self::with('order_detail.product.stock', 'order_detail.product.manufacturer', 'order_detail.product_attribute.stock', 'customer', 'delivery.country', 'invoice.country.lang_en')->where('current_state', $id_current_state)->get();
        
        if( ( $id_current_state == 15 ) OR ( $id_current_state = 28 ) ){
            
            foreach($data AS $item){
                
                $extra_data_product = '';
                
                foreach($item->order_detail AS $detail){
                    
                    if($detail->product_attribute_id != 0){
                        
                        if( isset($detail->product_attribute->stock) ){
                            if( $detail->product_attribute->stock->quantity < 0 ){
                                $extra_data_product = [
                                    'id_product' => $detail->product_id,  
                                    'id_product_attribute' => $detail->product_attribute_id,  
                                    'reference' => $detail->product_reference, 
                                    'brand' => $detail->product->manufacturer->name, 
                                    'sold' => $detail->product_quantity
                                ];
                            }
                        }else{
                            $extra_data_product = [
                                'id_product' => $detail->product_id,  
                                'id_product_attribute' => $detail->product_attribute_id,  
                                'reference' => $detail->product_reference, 
                                'brand' => $detail->product->manufacturer->name, 
                                'sold' => $detail->product_quantity
                            ];
                        }
                        
                    }else{
                        
                        if( isset($detail->product->stock) ){
                            if( $detail->product->stock->quantity < 0 ){
                                $extra_data_product = [
                                    'id_product' => $detail->product_id,  
                                    'id_product_attribute' => 0,  
                                    'reference' => $detail->product_reference, 
                                    'brand' => $detail->product->manufacturer->name, 
                                    'sold' => $detail->product_quantity
                                ];
                            }
                        }else{
                            $extra_data_product = [
                                'id_product' => $detail->product_id,  
                                'id_product_attribute' => 0,  
                                'reference' => $detail->product_reference, 
                                'brand' => $detail->product->manufacturer->name, 
                                'sold' => $detail->product_quantity
                            ];
                        }
                    }
                }
                
                $item->extraDataField = $extra_data_product;
                
            }
        }
        return $data;
    }

    public static function getSoldItems($reference){

        $time = strtotime("-1 year", time());
        $date = date("Y-m-d", $time);

        $soldASM = DB::table(env('DB2_DB_prefix') . 'orders')
            ->select(     env('DB2_DB_prefix') . 'order_detail.product_quantity AS product_quantity')
            ->join(       env('DB2_DB_prefix') . 'order_detail',     env('DB2_DB_prefix') . 'orders.id_order', '=', env('DB2_DB_prefix') . 'order_detail.id_order')
            ->where(      env('DB2_DB_prefix') . 'order_detail.product_reference', '"' . $reference . '"' )
            ->where(      env('DB2_DB_prefix') . 'orders.date_add','>', '"' . $date . '"' )
            ->groupBy(    env('DB2_DB_prefix') . 'order_detail.product_reference')
            ->sum('product_quantity');

        $soldASD = 0;

        return $soldASM + $soldASD;
    }

    public static function dashboard_without_tracking($type){

        $data = array();
        $array = asm_dashboard::getExceptions('orders_without_tracking');
        
        $bd_data = self::select('ps_orders.id_order', 'ps_orders.reference', 'ps_order_state_lang.name AS state')
                        ->join('ps_order_state_lang', 'ps_orders.current_state', '=', 'ps_order_state_lang.id_order_state')
                        ->join('ps_order_carrier', 'ps_orders.id_order', '=', 'ps_order_carrier.id_order')
                        ->where('ps_order_carrier.tracking_number', '')
                        ->whereNotIn('ps_orders.id_order', $array)
                        ->whereIN('ps_orders.current_state', [4, 28])
                        ->groupBy('ps_orders.id_order')
                        ->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'state' => $item->state];
        
        return [
            'name'              => trans('dashboard.ORDER WITHOUT TRACKING'),
            'col'               => 4,
            'item_id'           => $type . '_orders_without_tracking',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['clean', 'id_order', 'reference', 'state'],
            'exception_fields'  => ['orders_without_tracking', 'id_order', 'reference', 'state'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function dashboard_waiting_info($type){

        $data = array();


        $bd_data = self::select('id_order', 'reference')->where('current_state', 30)->get();

        foreach($bd_data AS $item) $data[] = ['id_order' => $item->id_order, 'reference' => $item->reference];
        
        return [
            'name'              => trans('dashboard.ORDERS - WAITING INFO'),
            'col'               => 4,
            'item_id'           => $type . '_waiting_info',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['id_order', 'reference'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    } 

    public static function dashboard_duplicated_orders($type){

        $data = array();
        $array = asm_dashboard::getExceptions('duplicated_order');
        
        $bd_data = self::select('id_order', 'reference', 'total_paid', 'id_customer', DB::raw('count(*) AS repeated'))
            ->whereNotIn('id_order', $array)
            ->groupBy('id_customer', 'payment', 'total_paid', DB::raw('DATE(date_add)'))
            ->havingRaw(' count(*) > 1')  
            ->orderBy('id_order')
            ->get();

        foreach($bd_data AS $item) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'total_paid' => $item->total_paid, 'repeated' => $item->repeated];
        
        return [
            'name'              => trans('dashboard.DUPLICATED ORDER'),
            'col'               => 4,
            'item_id'           => $type . '_duplicated_order',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['clean', 'id_order', 'reference', 'total_paid', 'repeated'],
            'exception_fields'  => ['duplicated_order', 'id_order', 'reference', 'repeated'],
            'counter'           => count($data),
            'data'              => $data
        ];       
    } 

    public static function dashboard_duplicated_status($type){

        $data = array();
        $array = asm_dashboard::getExceptions('payment_accept');

        $bd_data = order_history::getPanelInfo($array);
        
        foreach($bd_data AS $item){
            if( $item->total > 1) $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'client' => $item->firstname . ' ' . $item->lastname, 'total' => $item->total];
        }
        
        return [
            'name'              => trans('dashboard.DUPLICATED STATUS'),
            'col'               => 4,
            'item_id'           => $type . '_duplicated_status',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['clean', 'id_order', 'reference', 'client', 'total'],
            'exception_fields'  => ['payment_accept', 'id_order', 'reference', 'client'],
            'counter'           => count($data),
            'data'              => $data
        ];  ;        
    } 

    public static function dashboard_returns_warranties($type){

        $data = array();
        $orders = self::select('id_order', 'reference')->where('current_state', 29)->get();

        foreach($orders AS $order) $data[] = ['id_order' => $order->id_order, 'reference' => $order->reference, 'products' => orders_details::getProductsOfOrder($order->id_order)];

        return [
            'name'              => trans('dashboard.RETURNS & WARRANTIES OK'),
            'col'               => 4,
            'item_id'           => $type . '_returns_warranties',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['id_order', 'reference', 'products'],
            'counter'           => count($data),
            'data'              => $data
        ];        
    }

    public static function shippingPaidByCustomer($year){
        
        $total_order = self::select( DB::raw('sum(ps_orders.total_shipping_tax_incl) AS total_shipping') )
            ->where('ps_orders.date_add', '>', date('Y') . '-01-01 00:00:00')
            ->whereIn('ps_orders.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->value('total_shipping');
        
        $total_by_carrier = self::select( 'ps_carrier.name AS name', DB::raw('sum(ps_orders.total_shipping_tax_incl) AS total_shipping') )
            ->leftjoin( 'ps_carrier', 'ps_carrier.id_carrier', '=', 'ps_orders.id_carrier')
            ->where('ps_orders.date_add', '>', date('Y') . '-01-01 00:00:00')
            ->whereIn('ps_orders.current_state', [2, 3, 4, 5, 15, 16, 28])
            ->groupBy('ps_carrier.id_reference')
            ->get();

        $carrier_data['DPD'] = 0;
        $carrier_data['UPS'] = 0;
        $carrier_data['TNT'] = 0;
        $carrier_data['NACEX'] = 0;
        $carrier_data['GLS'] = 0;
        $carrier_data['FEDEX'] = 0;
        
        foreach($total_by_carrier AS $carrier){
            
            if (strpos($carrier['name'],"DPD") !== false)   $carrier_data['DPD']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"UPS") !== false)   $carrier_data['UPS']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"TNT") !== false)   $carrier_data['TNT']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"NACEX") !== false) $carrier_data['NACEX'] += $carrier['total_shipping'];
            if (strpos($carrier['name'],"GLS") !== false)   $carrier_data['GLS']   += $carrier['total_shipping'];
            if (strpos($carrier['name'],"FEDEX") !== false) $carrier_data['FEDEX'] += $carrier['total_shipping'];

        }

        $carrier_data['total'] = $total_order;

        return $carrier_data;
    }

    public static function getCounters($id_shop, $expectedEvolution){
        
        if($id_shop == 1){

            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'https://www.all-stars-distribution.com/custom/api/dashboards/main_dashboard.php');
            
            $stream_data = array();            
            if($response->getStatusCode() == 200) $stream_data = json_decode($response->getBody(), true);

            $data['awaiting']   = (int)$stream_data['data']['awaiting'];
            $data['packing']    = (int)$stream_data['data']['packing'];
            $data['shipped']    = (int)$stream_data['data']['shipped'];
            $data['warranty']   = (int)$stream_data['data']['warranty'];
            $data['backorders'] = (int)$stream_data['data']['backorders'];
            $data['partial']    = (int)$stream_data['data']['partial'];
            $data['pending']    = (int)$stream_data['data']['pending'];
            $data['today_forcast'] = (float)$stream_data['data']['today_forcast'] * $expectedEvolution;
            $data['today_realized'] = (float)$stream_data['data']['today_realized'];
            $data['yesterday_forcast'] = (float)$stream_data['data']['yesterday_forcast'] * $expectedEvolution;
            $data['yesterday_realized'] = (float)$stream_data['data']['yesterday_realized'];

        }else if($id_shop == 2){
            
            $yesterday = order_payment::getASMTotals(0);
            $today = order_payment::getASMTotals(1);
            
            $data['awaiting'] = 0;
            $data['packing'] = self::where('current_state', 3)->count();
            $data['shipped'] = self::join('ps_order_history', 'ps_order_history.id_order', 'ps_orders.id_order')->where('ps_orders.current_state', 4)->where('ps_order_history.id_order_state', 4)->where('ps_order_history.date_add', '>', date('Y') . '-' . date('m') . '-' . date('d')  . ' 00:00:00')->count();
            $data['warranty'] = self::where('current_state', 29)->count();
            $data['backorders'] = self::where('current_state', 15)->count();
            $data['partial'] = self::where('current_state', 28)->count();
            $data['pending'] = self::where('current_state', 30)->count();
            $data['today_forcast'] = (float)$today->homologue_day * $expectedEvolution;
            $data['today_realized'] = (float)$today->day;
            $data['yesterday_forcast'] = (float)$yesterday->homologue_day * $expectedEvolution;
            $data['yesterday_realized'] = (float)$yesterday->day;

        }else if($id_shop == 3){
            
            $data['awaiting'] = 0;
            $data['packing'] = 0;
            $data['shipped'] = 0;
            $data['warranty'] = 0;
            $data['backorders'] = 0;
            $data['partial'] = 0;
            $data['pending'] = 0;
            $data['today_forcast'] = 0;
            $data['today_realized'] = 0;
            $data['yesterday_forcast'] = 0;
            $data['yesterday_realized'] = 0;
            
        }else if($id_shop == 4){
            
            $data['awaiting'] = 0;
            $data['packing'] = 0;
            $data['shipped'] = 0;
            $data['warranty'] = 0;
            $data['backorders'] = 0;
            $data['partial'] = 0;
            $data['pending'] = 0;
            $data['today_forcast'] = 0;
            $data['today_realized'] = 0;
            $data['yesterday_forcast'] = 0;
            $data['yesterday_realized'] = 0;
            
        }
    
        return (object)$data;
        
    }

    public static function dashboard_orders_without_payment($type){

        $data = array();

        $bd_data = order_history::getOrdersWithoutPayment();

        foreach($bd_data AS $item){
            $data[] = ['clean' => $item->id_order, 'id_order' => $item->id_order, 'reference' => $item->reference, 'client' => $item->firstname . ' ' . $item->lastname];
        }
        
        return [
            'name'              => trans('dashboard.Without payment'),
            'col'               => 4,
            'item_id'           => $type . '_without_payment',
            'prestashop'        => [ 'token' => Config::get('token')->AdminOrders, 'controller' => 'AdminOrders', 'element' => 'id_order', 'extraParameters' => '&vieworder' ],
            'columns'           => ['id_order', 'reference', 'client'],
            'counter'           => count($data),
            'data'              => $data
        ];  ;        
    } 

    public static function dashboard_customers_by_last_valid_order($type, $id_shop){

        $data = array();

        $bd_data = self::select('firstname', 'lastname', DB::raw('ps_orders.date_add AS date_order'))
            ->join('ps_customer', 'ps_orders.id_customer', '=', 'ps_customer.id_customer')
            ->whereIN('current_state', [3, 4, 5, 7, 9, 21, 22])
            ->orderBy('id_order')
            ->groupBy('ps_orders.id_customer')
            ->get();
        
        foreach($bd_data AS $item){
            $data[] = ['client' => $item->firstname . ' ' . $item->lastname, 'date' => $item->date_order];
        }
        
        return [
            'name'              => trans('dashboard.clients by last valid order'),
            'col'               => 4,
            'item_id'           => $type . '_las_valid_order',
            'columns'           => ['client', 'date'],
            'counter'           => count($data),
            'data'              => $data
        ];  ;        
    } 





}
