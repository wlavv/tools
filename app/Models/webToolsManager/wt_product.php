<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

use App\Models\webToolsManager\wt_orders_details;

class product extends Model{
    
    protected $connection = 'mysql2';
    use HasFactory;
    public $timestamps = false;
    protected $primaryKey = 'id_product';

    public function __construct(){
        $this->table = env('DB2_prefix')."product";
    }
    
}