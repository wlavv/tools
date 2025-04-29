<?php

namespace App\Models\webToolsManager;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wt_tasks extends Model
{
    use HasFactory;
    protected $fillable = ['name'];
    public $timestamps = false;

    public function __construct(){
        $this->table = "wt_tasks";
    }    
    
    public static function getTasks($type, $name){
        return wt_tasks::where('type', $type)->where('name', $name)->get();
    }
}
