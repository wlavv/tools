<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Models\webToolsManager\wt_orders_details;

class wt_product extends Model{
    
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'id_product';

    public function __construct(){
        $this->table = env('DB2_prefix')."product";
    }

    public static function exists($reference){

        return wt_product::where('reference', $reference)->first();

    }

    public static function addProduct($data){

        $product = self::exists($data['reference']);

        if(isset($product->reference)){
            self::updateProduct($data, $product->id_product);
            return $product->id_product;
        }else{
            return self::add($data);
        }
    }

    public static function add($data){

        $row = new wt_product();
        $row->reference = $data['reference'];
        $row->price = $data['price'];
        $row->name  = $data['title'];
        $row->image = $data['thumb'];
        $row->save();

        return $row->id_product;
    }

    public static function updateProduct($data, $id_product){

        wt_product::where('id_product', $id_product)->update([
            'price' => $data['price'],
            'name'  => $data['title'],
            'image' => $data['thumb'],
        ]);

        return $id_product;
    }

}