<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wt_customer extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct(){
        $this->table = env('DB2_prefix')."customers";
     }

    public static function addNew($data){
        $customer = new wt_customer();
        $customer->firstname = $data->firstname;
        $customer->lastname = $data->lastname;
        $customer->email = $data->email;
        $customer->phone = $data->phone;
        $customer->save();
        
        return 1;
    }

    public static function active($id_customer, $active){
        return wt_customer::where('id_customer', $id_customer)->update(['active' => $active]);
    }

    public static function destroy($id_customer){
        return wt_customer::where('id_customer', $id_customer)->delete();
    }

    public static function getToEdit($id_customer){
        return wt_customer::where('id_customer', $id_customer)->first();
    }

    public static function updateRegister($data, $id){
        return wt_customer::where('id_customer', $id)->update(
            [
                'firstname' => $data->firstname,
                'lastname'  => $data->lastname,
                'email'     => $data->email,
                'phone'     => $data->phone,
            ]
        );
    }

    public static function getCustomer($tag){
        return wt_customer::where('id_customer', $tag)->orWhere('email', 'LIKE', '%' . $tag . '%')->orWhere('phone', 'LIKE', '%' . $tag . '%')->get();
    }
    
    
}