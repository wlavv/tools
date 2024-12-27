<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

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

    public static function addProduct($id_order, $data){

        $row = new wt_orders_details();

        $row->id_order = $id_order;
        $row->id_product = '';
        $row->id_product_attribute = '';
        $row->reference = $data['reference'];
        $row->unitary_price = $data['price'];
        $row->quantity = 1;
        $row->price = $data['price'];
        $row->save();

        return 1;
    }

    
}
