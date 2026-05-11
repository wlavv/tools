<?php

namespace Modules\CatalogManager\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogAiGeneration extends Model
{
    protected $table = 'catalog_ai_generations';

    protected $guarded = [];

    protected $casts = ['input_payload' => 'array', 'output_payload' => 'array', 'applied' => 'boolean', 'applied_at' => 'datetime'];

}
