<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class wt_password_manager extends Model
{
    use HasFactory;
    protected $fillable = ['project', 'username', 'password'];

    public function __construct(){
        $this->table = env('DB2_prefix')."password_manager";
    }

    public static function addData($request){

        $credentials = new wt_password_manager();
        $credentials->project = $request->project;
        $credentials->username = $request->username;
        $credentials->password = $request->password;
        $credentials->save();

        return 1;

    }

    public static function updateData($request){

        $credentials = self::where('id', $request->id)->first();
        $credentials->project = $request->project;
        $credentials->username = $request->username;
        $credentials->password = $request->password;
        $credentials->save();

        return 1;
    }

    public static function destroyData($request){

        return self::where('id', $request->id)->delete();

    }

}
