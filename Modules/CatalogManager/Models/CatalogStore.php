<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogStore extends Model
{
    protected $table = 'catalog_stores';

    protected $guarded = [];

    protected $casts = ['settings' => 'array'];

}
