<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogLog extends Model
{
    protected $table = 'lsg_catalog_logs';
    protected $fillable = ['loggable_type','loggable_id','event','severity','title','message','payload','user_id'];
    protected $casts = ['payload' => 'array'];
}
