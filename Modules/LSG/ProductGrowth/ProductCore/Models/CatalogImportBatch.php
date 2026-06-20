<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogImportBatch extends Model
{
    protected $table = 'lsg_catalog_import_batches';
    protected $fillable = ['filename','source','store_id','rows_total','rows_imported','rows_failed','status','errors','created_by'];
    protected $casts = ['errors' => 'array'];
}
