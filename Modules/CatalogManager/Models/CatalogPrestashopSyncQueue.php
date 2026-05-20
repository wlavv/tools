<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogPrestashopSyncQueue extends Model
{
    protected $table = 'catalog_prestashop_sync_queue';

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = ['payload' => 'array', 'processed_at' => 'datetime'];

}
