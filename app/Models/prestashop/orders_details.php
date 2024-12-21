<?php

namespace App\Models\prestashop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Config;

class orders_details extends Model
{   
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct()
    {
        $this->table = env('DB2_prefix')."order_details";
    }

    public static function addProduct($id_order, $data){

        $row = new orders_details();

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
